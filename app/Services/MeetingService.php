<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\User;
use App\Models\Company;
use App\Services\WherebyService;
use App\Services\NotificationService;
use App\Events\MeetingCreated;
use App\Events\MeetingAccepted;
use App\Events\MeetingDeclined;
use App\Events\MeetingCancelled;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MeetingService
{
    protected $wherebyService;
    protected $notificationService;

    public function __construct(
        WherebyService $wherebyService,
        NotificationService $notificationService
    ) {
        $this->wherebyService = $wherebyService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a member-to-member meeting
     */
    public function createMemberToMemberMeeting(
        User $organizer,
        User $otherUser,
        array $data
    ) {
        // Validate connection exists
        if (!$this->hasConnection($organizer, $otherUser)) {
            throw new \Exception('Users must be connected to schedule a meeting');
        }

        DB::beginTransaction();

        try {
            // Create meeting
            $meeting = Meeting::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'meeting_type' => 'member_to_member',
                'scheduled_at' => Carbon::parse($data['scheduled_at']),
                'duration_minutes' => $data['duration_minutes'] ?? 60,
                'timezone' => $data['timezone'] ?? 'UTC',
                'organizer_id' => $organizer->id,
                'user_id' => $otherUser->id,
                'location' => $data['location'] ?? null,
                'status' => 'pending',
            ]);

            // Add participants
            $meeting->participants()->create([
                'user_id' => $organizer->id,
                'role' => 'organizer',
                'status' => 'accepted',
                'invited_at' => now(),
                'responded_at' => now(),
            ]);

            $meeting->participants()->create([
                'user_id' => $otherUser->id,
                'role' => 'attendee',
                'status' => 'invited',
                'invited_at' => now(),
            ]);

            DB::commit();

            // Broadcast event
            event(new MeetingCreated($meeting));

            // Send notification
            $this->notificationService->sendMeetingInvitation(
                $otherUser,
                $meeting
            );

            return $meeting;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create member-to-member meeting', [
                'organizer_id' => $organizer->id,
                'other_user_id' => $otherUser->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a member-to-company meeting
     */
    public function createMemberToCompanyMeeting(
        User $organizer,
        Company $company,
        array $data
    ) {
        DB::beginTransaction();

        try {
            // Create meeting
            $meeting = Meeting::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'meeting_type' => 'member_to_company',
                'scheduled_at' => Carbon::parse($data['scheduled_at']),
                'duration_minutes' => $data['duration_minutes'] ?? 60,
                'timezone' => $data['timezone'] ?? 'UTC',
                'organizer_id' => $organizer->id,
                'company_id' => $company->id,
                'location' => $data['location'] ?? null,
                'status' => 'pending',
            ]);

            // Add organizer as participant
            $meeting->participants()->create([
                'user_id' => $organizer->id,
                'role' => 'organizer',
                'status' => 'accepted',
                'invited_at' => now(),
                'responded_at' => now(),
            ]);

            // Add company representatives
            $companyUsers = $company->users()->where('role', 'admin')->get();
            foreach ($companyUsers as $user) {
                $meeting->participants()->create([
                    'user_id' => $user->id,
                    'role' => 'attendee',
                    'status' => 'invited',
                    'invited_at' => now(),
                ]);

                // Send notification to each company representative
                $this->notificationService->sendMeetingInvitation(
                    $user,
                    $meeting
                );
            }

            DB::commit();

            // Broadcast event
            event(new MeetingCreated($meeting));

            return $meeting;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create member-to-company meeting', [
                'organizer_id' => $organizer->id,
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Accept a meeting
     */
    public function acceptMeeting(Meeting $meeting, User $user)
    {
        DB::beginTransaction();

        try {
            Log::info('acceptMeeting: before', [
                'meeting_id' => $meeting->id,
                'status' => $meeting->status,
                'scheduled_at' => optional($meeting->scheduled_at)->toISOString(),
                'user_id' => $user->id,
            ]);

            // Update participant status
            $participant = $meeting->participants()
                ->where('user_id', $user->id)
                ->firstOrFail();

            $participant->accept();

            // If this is member-to-member and both accepted, accept meeting
            if ($meeting->meeting_type === 'member_to_member') {
                $allAccepted = $meeting->participants()
                    ->where('status', '!=', 'accepted')
                    ->doesntExist();

                if ($allAccepted) {
                    $meeting->accept();
                    
                    // Create Whereby room for accepted meeting
                    $this->createWherebyRoom($meeting);
                }
            } else {
                // For member-to-company, accept if at least one company rep accepted
                $companyRepAccepted = $meeting->participants()
                    ->whereHas('user', function($query) use ($meeting) {
                        $query->whereHas('companies', function($q) use ($meeting) {
                            $q->where('companies.id', $meeting->company_id);
                        });
                    })
                    ->where('status', 'accepted')
                    ->exists();

                if ($companyRepAccepted && $meeting->isPending()) {
                    $meeting->accept();
                    $this->createWherebyRoom($meeting);
                }
            }

            DB::commit();

            $meeting->refresh();
            Log::info('acceptMeeting: after', [
                'meeting_id' => $meeting->id,
                'status' => $meeting->status,
                'scheduled_at' => optional($meeting->scheduled_at)->toISOString(),
                'user_id' => $user->id,
            ]);

            // Broadcast event
            event(new MeetingAccepted($meeting, $user));

            // Update the original invitation notification for the user who accepted
            $this->notificationService->updateMeetingInvitationNotification(
                $user,
                $meeting,
                'accepted'
            );

            // Send notification to organizer
            $this->notificationService->sendMeetingAccepted(
                $meeting->organizer,
                $meeting,
                $user
            );

            return $meeting;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Decline a meeting
     */
    public function declineMeeting(Meeting $meeting, User $user, string $reason = null)
    {
        DB::beginTransaction();

        try {
            $participant = $meeting->participants()
                ->where('user_id', $user->id)
                ->firstOrFail();

            $participant->decline();

            // If organizer declines, cancel the meeting
            if ($user->id === $meeting->organizer_id) {
                $meeting->cancel();
            } else {
                // If required participant declines, decline meeting
                if ($participant->role === 'attendee' && 
                    $meeting->meeting_type === 'member_to_member') {
                    $meeting->decline();
                }
            }

            DB::commit();

            // Broadcast event
            event(new MeetingDeclined($meeting, $user));

            // Update the original invitation notification for the user who declined
            $this->notificationService->updateMeetingInvitationNotification(
                $user,
                $meeting,
                'declined'
            );

            // Send notification
            $this->notificationService->sendMeetingDeclined(
                $meeting->organizer,
                $meeting,
                $user
            );

            return $meeting;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create Whereby room for meeting
     */
    protected function createWherebyRoom(Meeting $meeting)
    {
        try {
            $endDate = $meeting->scheduled_at
                ->copy()
                ->addMinutes($meeting->duration_minutes + 30); // 30 min buffer

            $wherebyResult = $this->wherebyService->createMeeting([
                'roomNamePrefix' => 'meeting',
                'roomNamePattern' => 'human-short',
                'endDate' => $endDate->toISOString(),
            ]);

            if ($wherebyResult['success']) {
                $meeting->update([
                    'whereby_meeting_id' => $wherebyResult['meeting_id'],
                    'room_url' => $wherebyResult['room_url'],
                    'host_room_url' => $wherebyResult['host_room_url'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create Whereby room for meeting', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if users have connection
     */
    protected function hasConnection(User $user1, User $user2)
    {
        return \App\Models\UserConnection::where(function($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                  ->where('receiver_id', $user2->id)
                  ->orWhere(function($q) use ($user1, $user2) {
                      $q->where('sender_id', $user2->id)
                        ->where('receiver_id', $user1->id);
                  });
        })
        ->where('status', 'accepted')
        ->exists();
    }

    /**
     * Start a meeting
     */
    public function startMeeting(Meeting $meeting)
    {
        // if (!$meeting->canBeJoined()) {
        //     throw new \Exception('Meeting cannot be started at this time');
        // }

        $meeting->start();

        // Send notifications to participants
        foreach ($meeting->acceptedParticipants as $participant) {
            $this->notificationService->sendMeetingStarted(
                $participant->user,
                $meeting
            );
        }

        return $meeting;
    }

    /**
     * Complete a meeting
     */
    public function completeMeeting(Meeting $meeting)
    {
        $meeting->complete();

        // Send completion notifications
        foreach ($meeting->acceptedParticipants as $participant) {
            $this->notificationService->sendMeetingCompleted(
                $participant->user,
                $meeting
            );
        }

        return $meeting;
    }
}