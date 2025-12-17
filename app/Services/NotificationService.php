<?php

namespace App\Services;

use App\Mail\MeetingInvitationMail;
use App\Mail\MeetingReminderMail;
use App\Mail\PasswordResetMail;
use App\Mail\WelcomeMail;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class NotificationService
{

    /**
     * Send a system notification to specific users or roles.
     */
    public function sendSystemNotification(string $title, string $message, array $data = [], string $severityType = 'info', $users = null)
    {
        try {
            $notification = new SystemNotification($title, $message, $data, $severityType);
            
            // If no specific users provided, send to all admins (only admin role, not manager)
            if (!$users) {
                $users = User::role(['admin'])->get();
            }
            
            $notification->broadcastToUsers($users);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send system notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email if enabled via config.
     */
    public function sendWelcomeEmail(User $user): bool
    {
        if (!config('mail-branding.send_welcome')) {
            return false;
        }

        try {
            // Generate password reset token for "Get Started" link
            $token = Password::broker()->createToken($user);
            
            // Create reset URL in the same format as password reset emails
            $frontend = rtrim(env('FRONTEND_URL', config('app.url')), '/');
            $resetUrl = $frontend . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            
            Mail::to($user->email)->queue(new WelcomeMail($user->name, $resetUrl));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email with custom template.
     */
    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        try {
            $frontend = rtrim(env('FRONTEND_URL', config('app.url')), '/');
            $resetUrl = $frontend . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

            Mail::to($user->email)->queue(new PasswordResetMail($user->name, $resetUrl));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting invitation notification
     */
    public function sendMeetingInvitation(User $user, Meeting $meeting)
    {
        try {
            $title = 'New Meeting Invitation';
            $message = "You have been invited to a meeting: {$meeting->title}";
            
            // Load relationships if not already loaded
            $meeting->load(['organizer', 'user', 'company', 'participants.user']);
            
            $data = [
                'type' => 'meeting_invitation',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->meeting_type,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'duration_minutes' => $meeting->duration_minutes,
                'timezone' => $meeting->timezone,
                'location' => $meeting->location,
                'organizer' => [
                    'id' => $meeting->organizer->id,
                    'name' => $meeting->organizer->name,
                    'email' => $meeting->organizer->email,
                    'profile_image' => $meeting->organizer->profile_image,
                ],
                'status' => $meeting->status,
                'actions' => [
                    [
                        'label' => 'Accept',
                        'action' => 'accept',
                        'method' => 'POST',
                        'endpoint' => "/api/meetings/{$meeting->id}/accept",
                        'style' => 'primary',
                    ],
                    [
                        'label' => 'Decline',
                        'action' => 'decline',
                        'method' => 'POST',
                        'endpoint' => "/api/meetings/{$meeting->id}/decline",
                        'style' => 'danger',
                    ],
                    [
                        'label' => 'View Details',
                        'action' => 'view_details',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'secondary',
                    ],
                ],
            ];

            $notification = new SystemNotification($title, $message, $data, 'info');
            $notification->broadcastToUsers(collect([$user]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting invitation notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting invitation email.
     */
    public function sendMeetingInvitationEmail(User $user, Meeting $meeting): bool
    {
        try {
            $url = url("/meetings/{$meeting->id}");
            Mail::to($user->email)->queue(new MeetingInvitationMail($meeting, $user->name, $url));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting invitation email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting accepted notification
     */
    public function sendMeetingAccepted(User $organizer, Meeting $meeting, User $acceptedBy)
    {
        try {
            // Reload meeting to get latest status and room URLs
            $meeting->refresh();
            $meeting->load(['organizer', 'user', 'company', 'participants.user']);
            
            $title = 'Meeting Accepted';
            $message = "{$acceptedBy->name} has accepted your meeting invitation: {$meeting->title}";
            
            // Build actions array
            $actions = [
                [
                    'label' => 'View Details',
                    'action' => 'view_details',
                    'method' => 'GET',
                    'endpoint' => "/api/meetings/{$meeting->id}",
                    'style' => 'secondary',
                ],
            ];
            
            // If meeting is fully accepted and has room URL, add room access action
            if ($meeting->status === 'accepted' && $meeting->room_url) {
                $actions[] = [
                    'label' => 'View Room',
                    'action' => 'view_room',
                    'method' => 'GET',
                    'endpoint' => "/api/meetings/{$meeting->id}",
                    'style' => 'info',
                    'room_url' => $meeting->room_url,
                    'host_room_url' => $meeting->host_room_url,
                ];
            }
            
            // If meeting can be started, add start action (only for organizer)
            if ($meeting->canBeJoined() && $meeting->organizer_id === $organizer->id) {
                $actions[] = [
                    'label' => 'Start Meeting',
                    'action' => 'start_meeting',
                    'method' => 'POST',
                    'endpoint' => "/api/meetings/{$meeting->id}/start",
                    'style' => 'primary',
                ];
            }
            
            $data = [
                'type' => 'meeting_accepted',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->meeting_type,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'duration_minutes' => $meeting->duration_minutes,
                'timezone' => $meeting->timezone,
                'location' => $meeting->location,
                'status' => $meeting->status,
                'accepted_by' => [
                    'id' => $acceptedBy->id,
                    'name' => $acceptedBy->name,
                    'email' => $acceptedBy->email,
                    'profile_image' => $acceptedBy->profile_image,
                ],
                'organizer' => [
                    'id' => $meeting->organizer->id,
                    'name' => $meeting->organizer->name,
                    'email' => $meeting->organizer->email,
                    'profile_image' => $meeting->organizer->profile_image,
                ],
                'room_url' => $meeting->room_url,
                'host_room_url' => $meeting->host_room_url,
                'whereby_meeting_id' => $meeting->whereby_meeting_id,
                'actions' => $actions,
            ];

            $notification = new SystemNotification($title, $message, $data, 'success');
            $notification->broadcastToUsers(collect([$organizer]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting accepted notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting declined notification
     */
    public function sendMeetingDeclined(User $organizer, Meeting $meeting, User $declinedBy)
    {
        try {
            $meeting->load(['organizer', 'user', 'company', 'participants.user']);
            
            $title = 'Meeting Declined';
            $message = "{$declinedBy->name} has declined your meeting invitation: {$meeting->title}";
            
            $data = [
                'type' => 'meeting_declined',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->meeting_type,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'duration_minutes' => $meeting->duration_minutes,
                'timezone' => $meeting->timezone,
                'location' => $meeting->location,
                'status' => $meeting->status,
                'declined_by' => [
                    'id' => $declinedBy->id,
                    'name' => $declinedBy->name,
                    'email' => $declinedBy->email,
                    'profile_image' => $declinedBy->profile_image,
                ],
                'organizer' => [
                    'id' => $meeting->organizer->id,
                    'name' => $meeting->organizer->name,
                    'email' => $meeting->organizer->email,
                    'profile_image' => $meeting->organizer->profile_image,
                ],
                'actions' => [
                    [
                        'label' => 'View Details',
                        'action' => 'view_details',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'secondary',
                    ],
                ],
            ];

            $notification = new SystemNotification($title, $message, $data, 'warning');
            $notification->broadcastToUsers(collect([$organizer]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting declined notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting started notification
     */
    public function sendMeetingStarted(User $user, Meeting $meeting)
    {
        try {
            $meeting->load(['organizer', 'user', 'company']);
            
            $title = 'Meeting Started';
            $message = "The meeting '{$meeting->title}' has started";
            
            $isOrganizer = $meeting->organizer_id === $user->id;
            $roomUrl = $isOrganizer ? $meeting->host_room_url : $meeting->room_url;
            
            $actions = [
                [
                    'label' => 'Join Meeting',
                    'action' => 'join_meeting',
                    'method' => 'GET',
                    'endpoint' => "/api/meetings/{$meeting->id}",
                    'style' => 'primary',
                    'room_url' => $roomUrl,
                ],
                [
                    'label' => 'View Details',
                    'action' => 'view_details',
                    'method' => 'GET',
                    'endpoint' => "/api/meetings/{$meeting->id}",
                    'style' => 'secondary',
                ],
            ];
            
            $data = [
                'type' => 'meeting_started',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->meeting_type,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'duration_minutes' => $meeting->duration_minutes,
                'timezone' => $meeting->timezone,
                'location' => $meeting->location,
                'status' => $meeting->status,
                'started_at' => $meeting->started_at ? $meeting->started_at->toISOString() : now()->toISOString(),
                'room_url' => $meeting->room_url,
                'host_room_url' => $meeting->host_room_url,
                'whereby_meeting_id' => $meeting->whereby_meeting_id,
                'is_organizer' => $isOrganizer,
                'organizer' => [
                    'id' => $meeting->organizer->id,
                    'name' => $meeting->organizer->name,
                    'email' => $meeting->organizer->email,
                    'profile_image' => $meeting->organizer->profile_image,
                ],
                'actions' => $actions,
            ];

            $notification = new SystemNotification($title, $message, $data, 'info');
            $notification->broadcastToUsers(collect([$user]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting started notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting reminder email.
     */
    public function sendMeetingReminderEmail(User $user, Meeting $meeting, ?string $joinUrl = null): bool
    {
        try {
            $joinUrl = $joinUrl ?: url("/meetings/{$meeting->id}");
            Mail::to($user->email)->queue(new MeetingReminderMail($meeting, $user->name, $joinUrl));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting reminder email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting completed notification
     */
    public function sendMeetingCompleted(User $user, Meeting $meeting)
    {
        try {
            $meeting->load(['organizer', 'user', 'company']);
            
            $title = 'Meeting Completed';
            $message = "The meeting '{$meeting->title}' has been completed";
            
            $data = [
                'type' => 'meeting_completed',
                'meeting_id' => $meeting->id,
                'meeting_type' => $meeting->meeting_type,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'scheduled_at' => $meeting->scheduled_at->toISOString(),
                'duration_minutes' => $meeting->duration_minutes,
                'timezone' => $meeting->timezone,
                'location' => $meeting->location,
                'status' => $meeting->status,
                'completed_at' => $meeting->completed_at ? $meeting->completed_at->toISOString() : now()->toISOString(),
                'started_at' => $meeting->started_at ? $meeting->started_at->toISOString() : null,
                'organizer' => [
                    'id' => $meeting->organizer->id,
                    'name' => $meeting->organizer->name,
                    'email' => $meeting->organizer->email,
                    'profile_image' => $meeting->organizer->profile_image,
                ],
                'actions' => [
                    [
                        'label' => 'View Details',
                        'action' => 'view_details',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'secondary',
                    ],
                ],
            ];

            $notification = new SystemNotification($title, $message, $data, 'info');
            $notification->broadcastToUsers(collect([$user]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting completed notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update meeting invitation notification when accepted or declined
     */
    public function updateMeetingInvitationNotification(User $user, Meeting $meeting, string $responseStatus)
    {
        try {
            // Find the original invitation notification for this user and meeting
            $notification = \App\Models\UserNotification::where('user_id', $user->id)
                ->where('notification_type', 'system')
                ->whereJsonContains('data->type', 'meeting_invitation')
                ->whereJsonContains('data->meeting_id', $meeting->id)
                ->whereNull('read_at') // Only update unread notifications
                ->first();

            if (!$notification) {
                return false;
            }

            // Reload meeting to get latest status
            $meeting->refresh();
            $meeting->load(['organizer', 'user', 'company', 'participants.user']);

            // Update notification data
            $data = $notification->data;
            $data['status'] = $responseStatus; // 'accepted' or 'declined'
            $data['responded_at'] = now()->toISOString();
            
            // Ensure organizer profile_image is included (update if missing or changed)
            if (isset($data['organizer'])) {
                $data['organizer']['profile_image'] = $meeting->organizer->profile_image;
            }
            
            // Update actions based on response
            if ($responseStatus === 'accepted') {
                // Remove accept/decline actions, keep view details
                $data['actions'] = [
                    [
                        'label' => 'View Details',
                        'action' => 'view_details',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'secondary',
                    ],
                ];
                
                // If meeting is fully accepted, add room access
                if ($meeting->status === 'accepted' && $meeting->room_url) {
                    $isOrganizer = $meeting->organizer_id === $user->id;
                    $roomUrl = $isOrganizer ? $meeting->host_room_url : $meeting->room_url;
                    
                    $data['actions'][] = [
                        'label' => 'View Room',
                        'action' => 'view_room',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'info',
                        'room_url' => $meeting->room_url,
                        'host_room_url' => $meeting->host_room_url,
                    ];
                    
                    $data['room_url'] = $meeting->room_url;
                    $data['host_room_url'] = $meeting->host_room_url;
                }
                
                // Update message and severity
                $notification->message = "You have accepted the meeting invitation: {$meeting->title}";
                $notification->severity_type = 'success';
            } else {
                // Declined - remove all actions or keep only view details
                $data['actions'] = [
                    [
                        'label' => 'View Details',
                        'action' => 'view_details',
                        'method' => 'GET',
                        'endpoint' => "/api/meetings/{$meeting->id}",
                        'style' => 'secondary',
                    ],
                ];
                
                // Update message and severity
                $notification->message = "You have declined the meeting invitation: {$meeting->title}";
                $notification->severity_type = 'warning';
            }

            // Update notification
            $notification->data = $data;
            $notification->save();

            // Broadcast the update
            event(new \App\Events\NotificationUpdated($notification));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update meeting invitation notification: ' . $e->getMessage());
            return false;
        }
    }

} 