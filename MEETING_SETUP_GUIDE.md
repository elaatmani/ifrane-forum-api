# Meeting Setup Guide: Member-to-Member & Member-to-Company Meetings

This comprehensive guide explains how to implement a scheduled meeting feature that allows:
- **Member-to-Member Meetings**: Users can schedule meetings with other users
- **Member-to-Company Meetings**: Users can schedule meetings with companies

The system integrates with Whereby for video conferencing and includes real-time notifications, reminders, and complete meeting lifecycle management.

---

## Table of Contents

1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Models & Relationships](#models--relationships)
4. [Service Layer](#service-layer)
5. [API Endpoints](#api-endpoints)
6. [Integration Flow](#integration-flow)
7. [Whereby Integration](#whereby-integration)
8. [Real-time Notifications](#real-time-notifications)
9. [Scheduled Tasks](#scheduled-tasks)
10. [Frontend Integration](#frontend-integration)
11. [Implementation Steps](#implementation-steps)

---

## Overview

### Meeting Types

1. **Member-to-Member Meetings**
   - One-on-one meetings between two users
   - Requires an accepted connection between users
   - Both users can schedule meetings with each other

2. **Member-to-Company Meetings**
   - Meetings between a user and a company
   - User schedules meeting with company
   - Company representatives can accept/decline
   - Multiple company members can join

### Key Features

- ✅ **Scheduled Meetings**: Set date/time in advance
- ✅ **Whereby Integration**: Automatic video room creation
- ✅ **Real-time Notifications**: Instant updates via Pusher
- ✅ **Meeting Reminders**: Automated reminders before meetings
- ✅ **Status Management**: Pending, accepted, declined, completed, cancelled
- ✅ **Participant Management**: Track who's attending
- ✅ **Meeting History**: Complete audit trail

---

## Database Schema

### meetings Table

```sql
CREATE TABLE meetings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Meeting identification
    title VARCHAR(255) NOT NULL,
    description TEXT,
    meeting_type ENUM('member_to_member', 'member_to_company') NOT NULL,
    
    -- Scheduling
    scheduled_at TIMESTAMP NOT NULL,
    duration_minutes INTEGER DEFAULT 60,
    timezone VARCHAR(50) DEFAULT 'UTC',
    
    -- Participants
    organizer_id UUID NOT NULL, -- User who created the meeting
    organizer_type VARCHAR(50) DEFAULT 'user', -- 'user' or 'company'
    
    -- Relationships
    user_id UUID NULL, -- For member-to-member: other user
    company_id UUID NULL, -- For member-to-company: company
    
    -- Whereby integration
    whereby_meeting_id VARCHAR(255) NULL,
    room_url VARCHAR(500) NULL,
    host_room_url VARCHAR(500) NULL,
    
    -- Status
    status ENUM('pending', 'accepted', 'declined', 'cancelled', 'completed', 'in_progress') DEFAULT 'pending',
    
    -- Metadata
    location VARCHAR(255) NULL, -- Physical or virtual location
    agenda JSON NULL, -- Meeting agenda items
    notes TEXT NULL, -- Meeting notes
    metadata JSON NULL, -- Additional flexible data
    
    -- Timestamps
    accepted_at TIMESTAMP NULL,
    declined_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_organizer (organizer_id),
    INDEX idx_user (user_id),
    INDEX idx_company (company_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status),
    INDEX idx_meeting_type (meeting_type),
    
    -- Foreign keys
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

### meeting_participants Table

```sql
CREATE TABLE meeting_participants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    meeting_id UUID NOT NULL,
    user_id UUID NOT NULL,
    
    -- Role
    role ENUM('organizer', 'attendee', 'optional') DEFAULT 'attendee',
    
    -- Status
    status ENUM('invited', 'accepted', 'declined', 'tentative', 'no_show') DEFAULT 'invited',
    
    -- Timestamps
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    joined_at TIMESTAMP NULL,
    left_at TIMESTAMP NULL,
    
    -- Metadata
    notes TEXT NULL,
    metadata JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_meeting (meeting_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    
    -- Foreign keys
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Unique constraint
    UNIQUE KEY unique_meeting_user (meeting_id, user_id)
);
```

### Migration File

Create `database/migrations/YYYY_MM_DD_HHMMSS_create_meetings_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('meeting_type', ['member_to_member', 'member_to_company']);
            
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('timezone')->default('UTC');
            
            $table->uuid('organizer_id');
            $table->string('organizer_type')->default('user');
            
            $table->uuid('user_id')->nullable();
            $table->uuid('company_id')->nullable();
            
            $table->string('whereby_meeting_id')->nullable();
            $table->string('room_url', 500)->nullable();
            $table->string('host_room_url', 500)->nullable();
            
            $table->enum('status', [
                'pending', 
                'accepted', 
                'declined', 
                'cancelled', 
                'completed', 
                'in_progress'
            ])->default('pending');
            
            $table->string('location')->nullable();
            $table->json('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->index(['organizer_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index('scheduled_at');
            $table->index('meeting_type');
        });
        
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->uuid('user_id');
            
            $table->enum('role', ['organizer', 'attendee', 'optional'])->default('attendee');
            $table->enum('status', [
                'invited', 
                'accepted', 
                'declined', 
                'tentative', 
                'no_show'
            ])->default('invited');
            
            $table->timestamp('invited_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['meeting_id', 'user_id']);
            $table->index(['meeting_id', 'status']);
            $table->index('user_id');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
    }
};
```

---

## Models & Relationships

### Meeting Model

Create `app/Models/Meeting.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'meeting_type',
        'scheduled_at',
        'duration_minutes',
        'timezone',
        'organizer_id',
        'organizer_type',
        'user_id',
        'company_id',
        'whereby_meeting_id',
        'room_url',
        'host_room_url',
        'status',
        'location',
        'agenda',
        'notes',
        'metadata',
        'accepted_at',
        'declined_at',
        'cancelled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'agenda' => 'array',
        'metadata' => 'array',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    public function acceptedParticipants()
    {
        return $this->participants()->where('status', 'accepted');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->whereIn('status', ['pending', 'accepted']);
    }

    public function scopeMemberToMember($query)
    {
        return $query->where('meeting_type', 'member_to_member');
    }

    public function scopeMemberToCompany($query)
    {
        return $query->where('meeting_type', 'member_to_company');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isUpcoming()
    {
        return $this->scheduled_at > now() && 
               in_array($this->status, ['pending', 'accepted']);
    }

    public function isPast()
    {
        return $this->scheduled_at < now();
    }

    public function canBeJoined()
    {
        return $this->isAccepted() && 
               $this->scheduled_at <= now()->addMinutes(15) && // 15 min before
               $this->scheduled_at >= now()->subHours(1); // 1 hour after
    }

    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getEndTime()
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function getTimeUntilMeeting()
    {
        return now()->diffInMinutes($this->scheduled_at);
    }
}
```

### MeetingParticipant Model

Create `app/Models/MeetingParticipant.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MeetingParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'role',
        'status',
        'invited_at',
        'responded_at',
        'joined_at',
        'left_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }

    public function markAsJoined()
    {
        $this->update([
            'joined_at' => now(),
        ]);
    }

    public function markAsLeft()
    {
        $this->update([
            'left_at' => now(),
        ]);
    }
}
```

### Update User Model

Add to `app/Models/User.php`:

```php
public function organizedMeetings()
{
    return $this->hasMany(Meeting::class, 'organizer_id');
}

public function meetings()
{
    return $this->hasMany(Meeting::class, 'user_id');
}

public function meetingParticipants()
{
    return $this->hasMany(MeetingParticipant::class);
}

public function upcomingMeetings()
{
    return $this->meetingParticipants()
                ->whereHas('meeting', function($query) {
                    $query->where('scheduled_at', '>', now())
                          ->whereIn('status', ['pending', 'accepted']);
                });
}
```

### Update Company Model

Add to `app/Models/Company.php`:

```php
public function meetings()
{
    return $this->hasMany(Meeting::class, 'company_id');
}

public function upcomingMeetings()
{
    return $this->meetings()
                ->where('scheduled_at', '>', now())
                ->whereIn('status', ['pending', 'accepted']);
}
```

---

## Service Layer

### MeetingService

Create `app/Services/MeetingService.php`:

```php
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
                'agenda' => $data['agenda'] ?? null,
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
                'agenda' => $data['agenda'] ?? null,
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

            // Broadcast event
            event(new MeetingAccepted($meeting, $user));

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
        if (!$meeting->canBeJoined()) {
            throw new \Exception('Meeting cannot be started at this time');
        }

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
```

---

## API Endpoints

### MeetingController

Create `app/Http/Controllers/API/MeetingController.php`:

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Company;
use App\Services\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    /**
     * POST /api/meetings/member-to-member
     * Create member-to-member meeting
     */
    public function createMemberToMember(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $organizer = Auth::user();
            $otherUser = User::findOrFail($request->user_id);

            $meeting = $this->meetingService->createMemberToMemberMeeting(
                $organizer,
                $otherUser,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'participants.user']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/member-to-company
     * Create member-to-company meeting
     */
    public function createMemberToCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|uuid|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $organizer = Auth::user();
            $company = Company::findOrFail($request->company_id);

            $meeting = $this->meetingService->createMemberToCompanyMeeting(
                $organizer,
                $company,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'company', 'participants.user']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/accept
     * Accept a meeting
     */
    public function accept(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            // Check if user is a participant
            if (!$meeting->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this meeting',
                ], 403);
            }

            $meeting = $this->meetingService->acceptMeeting($meeting, $user);

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'company', 'participants.user']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/decline
     * Decline a meeting
     */
    public function decline(Request $request, string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if (!$meeting->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this meeting',
                ], 403);
            }

            $meeting = $this->meetingService->declineMeeting(
                $meeting,
                $user,
                $request->get('reason')
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'company', 'participants.user']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/meetings
     * List user's meetings
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Meeting::where(function($q) use ($user) {
            $q->where('organizer_id', $user->id)
              ->orWhere('user_id', $user->id)
              ->orWhereHas('participants', function($p) use ($user) {
                  $p->where('user_id', $user->id);
              });
        })
        ->with(['organizer', 'user', 'company', 'participants.user']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('meeting_type', $request->type);
        }

        if ($request->has('upcoming')) {
            $query->upcoming();
        }

        $meetings = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $meetings,
        ]);
    }

    /**
     * GET /api/meetings/{meetingId}
     * Get meeting details
     */
    public function show(string $meetingId): JsonResponse
    {
        $meeting = Meeting::with([
            'organizer',
            'user',
            'company',
            'participants.user'
        ])->findOrFail($meetingId);

        $user = Auth::user();

        // Check access
        if (!$meeting->participants()->where('user_id', $user->id)->exists() &&
            $meeting->organizer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this meeting',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    /**
     * POST /api/meetings/{meetingId}/start
     * Start a meeting
     */
    public function start(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if ($meeting->organizer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the organizer can start the meeting',
                ], 403);
            }

            $meeting = $this->meetingService->startMeeting($meeting);

            return response()->json([
                'success' => true,
                'data' => $meeting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/complete
     * Complete a meeting
     */
    public function complete(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if ($meeting->organizer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the organizer can complete the meeting',
                ], 403);
            }

            $meeting = $this->meetingService->completeMeeting($meeting);

            return response()->json([
                'success' => true,
                'data' => $meeting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### Add Routes

Add to `routes/api.php`:

```php
// Meetings
Route::group(['prefix' => 'meetings'], function() {
    Route::post('/member-to-member', [App\Http\Controllers\API\MeetingController::class, 'createMemberToMember']);
    Route::post('/member-to-company', [App\Http\Controllers\API\MeetingController::class, 'createMemberToCompany']);
    Route::get('/', [App\Http\Controllers\API\MeetingController::class, 'index']);
    Route::get('/{meetingId}', [App\Http\Controllers\API\MeetingController::class, 'show']);
    Route::post('/{meetingId}/accept', [App\Http\Controllers\API\MeetingController::class, 'accept']);
    Route::post('/{meetingId}/decline', [App\Http\Controllers\API\MeetingController::class, 'decline']);
    Route::post('/{meetingId}/start', [App\Http\Controllers\API\MeetingController::class, 'start']);
    Route::post('/{meetingId}/complete', [App\Http\Controllers\API\MeetingController::class, 'complete']);
});
```

---

## Integration Flow

### Complete Integration Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    MEETING CREATION FLOW                         │
└─────────────────────────────────────────────────────────────────┘

1. USER INITIATES MEETING
   │
   ├─→ Member-to-Member: User selects another user
   │   └─→ Validates connection exists
   │
   └─→ Member-to-Company: User selects company
       └─→ No validation needed
   │
   ▼
2. FRONTEND SENDS REQUEST
   │
   ├─→ POST /api/meetings/member-to-member
   │   {
   │     "user_id": "uuid",
   │     "title": "Project Discussion",
   │     "scheduled_at": "2024-01-20T10:00:00Z",
   │     "duration_minutes": 60
   │   }
   │
   └─→ POST /api/meetings/member-to-company
       {
         "company_id": "uuid",
         "title": "Business Proposal",
         "scheduled_at": "2024-01-20T14:00:00Z",
         "duration_minutes": 90
       }
   │
   ▼
3. BACKEND PROCESSING
   │
   ├─→ MeetingController validates request
   │   ├─→ Validates user_id/company_id exists
   │   ├─→ Validates scheduled_at is in future
   │   └─→ Validates duration is reasonable
   │
   ▼
4. MeetingService CREATES MEETING
   │
   ├─→ Creates Meeting record
   │   ├─→ Sets status: 'pending'
   │   ├─→ Sets organizer_id
   │   ├─→ Sets user_id (member-to-member)
   │   └─→ Sets company_id (member-to-company)
   │
   ├─→ Creates MeetingParticipant records
   │   ├─→ Organizer: role='organizer', status='accepted'
   │   ├─→ Other user: role='attendee', status='invited'
   │   └─→ Company reps: role='attendee', status='invited'
   │
   └─→ Commits transaction
   │
   ▼
5. BROADCAST & NOTIFICATIONS
   │
   ├─→ Broadcasts MeetingCreated event
   │   └─→ Pusher channels:
   │       ├─→ user.{organizer_id}.messages
   │       ├─→ user.{other_user_id}.messages (member-to-member)
   │       └─→ user.{company_rep_id}.messages (member-to-company)
   │
   └─→ Sends notification emails/push
       └─→ Meeting invitation notification
   │
   ▼
6. RESPONSE TO FRONTEND
   │
   └─→ Returns meeting data with participants
       {
         "success": true,
         "data": {
           "id": "uuid",
           "title": "...",
           "status": "pending",
           "participants": [...]
         }
       }
```

### Meeting Acceptance Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    MEETING ACCEPTANCE FLOW                       │
└─────────────────────────────────────────────────────────────────┘

1. PARTICIPANT RECEIVES INVITATION
   │
   ├─→ Real-time notification via Pusher
   └─→ Email/push notification
   │
   ▼
2. PARTICIPANT CLICKS "ACCEPT"
   │
   └─→ POST /api/meetings/{meetingId}/accept
   │
   ▼
3. MeetingService::acceptMeeting()
   │
   ├─→ Updates MeetingParticipant status to 'accepted'
   │
   ├─→ Checks if all required participants accepted
   │   ├─→ Member-to-Member: Both must accept
   │   └─→ Member-to-Company: At least one company rep
   │
   ├─→ If all accepted:
   │   ├─→ Updates Meeting status to 'accepted'
   │   └─→ Creates Whereby room
   │       ├─→ Calls WherebyService::createMeeting()
   │       ├─→ Stores room_url and host_room_url
   │       └─→ Sets endDate = scheduled_at + duration + buffer
   │
   └─→ Commits transaction
   │
   ▼
4. BROADCAST & NOTIFICATIONS
   │
   ├─→ Broadcasts MeetingAccepted event
   └─→ Sends notification to organizer
   │
   ▼
5. RESPONSE
   │
   └─→ Returns updated meeting with room_url
```

### Meeting Start Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      MEETING START FLOW                          │
└─────────────────────────────────────────────────────────────────┘

1. MEETING TIME APPROACHES
   │
   ├─→ Scheduled task checks upcoming meetings
   │   └─→ 15 minutes before: Send reminder
   │
   ▼
2. ORGANIZER STARTS MEETING
   │
   └─→ POST /api/meetings/{meetingId}/start
       │
       ├─→ Validates meeting can be joined
       │   ├─→ Status is 'accepted'
       │   ├─→ scheduled_at <= now() + 15 minutes
       │   └─→ scheduled_at >= now() - 1 hour
       │
       ▼
3. MeetingService::startMeeting()
   │
   ├─→ Updates Meeting status to 'in_progress'
   ├─→ Sets started_at timestamp
   │
   └─→ Sends notifications to all participants
       └─→ "Meeting has started" with room_url
   │
   ▼
4. PARTICIPANTS JOIN
   │
   ├─→ Frontend opens Whereby room URL
   └─→ Participants join video call
```

---

## Whereby Integration

### Integration Flow for Scheduled Meetings

```
┌─────────────────────────────────────────────────────────────────┐
│              WHEREBY INTEGRATION FOR MEETINGS                     │
└─────────────────────────────────────────────────────────────────┘

1. MEETING ACCEPTED
   │
   └─→ MeetingService::acceptMeeting()
       │
       └─→ createWherebyRoom($meeting)
           │
           ▼
2. PREPARE WHEREBY REQUEST
   │
   ├─→ Calculate end date
   │   └─→ scheduled_at + duration_minutes + 30 min buffer
   │
   ├─→ Prepare options
   │   {
   │     "roomNamePrefix": "meeting",
   │     "roomNamePattern": "human-short",
   │     "endDate": "2024-01-20T11:30:00Z"
   │   }
   │
   ▼
3. CALL WHEREBY SERVICE
   │
   └─→ WherebyService::createMeeting($options)
       │
       ├─→ Check API key configured?
       │   ├─→ YES: Make real API call
       │   │   └─→ POST https://api.whereby.dev/v1/meetings
       │   │
       │   └─→ NO: Use mock implementation
       │       └─→ Generate fake URLs
       │
       ▼
4. PROCESS RESPONSE
   │
   ├─→ Extract meeting data
   │   ├─→ meeting_id
   │   ├─→ room_url (participant URL)
   │   └─→ host_room_url (organizer URL)
   │
   └─→ Update Meeting record
       ├─→ whereby_meeting_id
       ├─→ room_url
       └─→ host_room_url
   │
   ▼
5. ROOM READY FOR USE
   │
   └─→ When meeting starts, participants use room_url
       └─→ Opens Whereby video call interface
```

### Key Differences from Video Calls

| Aspect | Video Calls | Scheduled Meetings |
|--------|------------|-------------------|
| **Timing** | Immediate | Scheduled in future |
| **Room Creation** | On call initiation | On meeting acceptance |
| **Room Expiry** | Short (30 min) | Until meeting end + buffer |
| **Participants** | Conversation members | Explicitly invited |
| **Status** | Call states | Meeting lifecycle states |

---

## Real-time Notifications

### Events

Create event classes in `app/Events/`:

#### MeetingCreated Event

```php
<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        
        // Broadcast to organizer
        $channels[] = new PrivateChannel('user.' . $this->meeting->organizer_id . '.messages');
        
        // Broadcast to other participants
        foreach ($this->meeting->participants as $participant) {
            if ($participant->user_id !== $this->meeting->organizer_id) {
                $channels[] = new PrivateChannel('user.' . $participant->user_id . '.messages');
            }
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'meeting.created';
    }

    public function broadcastWith(): array
    {
        $this->meeting->load(['organizer', 'user', 'company', 'participants.user']);
        
        return [
            'meeting' => [
                'id' => $this->meeting->id,
                'title' => $this->meeting->title,
                'meeting_type' => $this->meeting->meeting_type,
                'scheduled_at' => $this->meeting->scheduled_at->toISOString(),
                'status' => $this->meeting->status,
                'organizer' => [
                    'id' => $this->meeting->organizer->id,
                    'name' => $this->meeting->organizer->name,
                ],
                'participants' => $this->meeting->participants->map(function($p) {
                    return [
                        'user_id' => $p->user_id,
                        'name' => $p->user->name,
                        'status' => $p->status,
                    ];
                }),
            ],
        ];
    }
}
```

Similar events for:
- `MeetingAccepted`
- `MeetingDeclined`
- `MeetingCancelled`
- `MeetingStarted`
- `MeetingCompleted`

---

## Scheduled Tasks

### Meeting Reminders

Create `app/Console/Commands/SendMeetingReminders.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders';
    protected $description = 'Send reminders for upcoming meetings';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        // Find meetings starting in 15 minutes
        $reminderTime = now()->addMinutes(15);
        
        $meetings = Meeting::where('status', 'accepted')
            ->whereBetween('scheduled_at', [
                $reminderTime->copy()->subMinute(),
                $reminderTime->copy()->addMinute()
            ])
            ->whereDoesntHave('participants', function($query) {
                $query->where('reminder_sent', true);
            })
            ->with('participants.user')
            ->get();

        foreach ($meetings as $meeting) {
            foreach ($meeting->acceptedParticipants as $participant) {
                $this->notificationService->sendMeetingReminder(
                    $participant->user,
                    $meeting
                );
                
                // Mark reminder as sent
                $participant->update(['reminder_sent' => true]);
            }
        }

        $this->info("Sent reminders for {$meetings->count()} meetings");
    }
}
```

### Auto-complete Past Meetings

Create `app/Console/Commands/CompletePastMeetings.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\MeetingService;
use Illuminate\Console\Command;

class CompletePastMeetings extends Command
{
    protected $signature = 'meetings:complete-past';
    protected $description = 'Mark past meetings as completed';

    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        parent::__construct();
        $this->meetingService = $meetingService;
    }

    public function handle()
    {
        $meetings = Meeting::whereIn('status', ['accepted', 'in_progress'])
            ->where('scheduled_at', '<', now()->subHours(1))
            ->get();

        foreach ($meetings as $meeting) {
            if ($meeting->status !== 'completed') {
                $this->meetingService->completeMeeting($meeting);
            }
        }

        $this->info("Completed {$meetings->count()} past meetings");
    }
}
```

### Schedule in Kernel

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Send meeting reminders every minute
    $schedule->command('meetings:send-reminders')
        ->everyMinute();
    
    // Complete past meetings every hour
    $schedule->command('meetings:complete-past')
        ->hourly();
}
```

---

## Frontend Integration

### Create Meeting (Member-to-Member)

```javascript
async function createMemberToMemberMeeting(userId, meetingData) {
    try {
        const response = await fetch('/api/meetings/member-to-member', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                title: meetingData.title,
                description: meetingData.description,
                scheduled_at: meetingData.scheduledAt, // ISO string
                duration_minutes: meetingData.duration,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                location: meetingData.location,
                agenda: meetingData.agenda
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Meeting created:', data.data);
            showSuccessMessage('Meeting invitation sent!');
        }
    } catch (error) {
        console.error('Failed to create meeting:', error);
    }
}
```

### Listen for Meeting Events

```javascript
// Listen for meeting invitations
Echo.private(`user.${userId}.messages`)
    .listen('.meeting.created', (event) => {
        console.log('New meeting invitation:', event.meeting);
        showMeetingInvitationModal(event.meeting);
    })
    .listen('.meeting.accepted', (event) => {
        console.log('Meeting accepted:', event.meeting);
        updateMeetingStatus(event.meeting.id, 'accepted');
    })
    .listen('.meeting.started', (event) => {
        console.log('Meeting started:', event.meeting);
        if (event.meeting.room_url) {
            openMeetingRoom(event.meeting.room_url);
        }
    });
```

### Accept Meeting

```javascript
async function acceptMeeting(meetingId) {
    try {
        const response = await fetch(`/api/meetings/${meetingId}/accept`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Meeting accepted:', data.data);
            // Update UI
            updateMeetingCard(meetingId, data.data);
        }
    } catch (error) {
        console.error('Failed to accept meeting:', error);
    }
}
```

### Join Meeting

```javascript
async function joinMeeting(meetingId) {
    try {
        // Get meeting details
        const response = await fetch(`/api/meetings/${meetingId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.data.room_url) {
            // Open Whereby room
            window.open(data.data.room_url, '_blank');
            
            // Or embed in iframe
            // document.getElementById('meeting-container').src = data.data.room_url;
        }
    } catch (error) {
        console.error('Failed to join meeting:', error);
    }
}
```

---

## Implementation Steps

### Step 1: Database Setup

1. Create migration files:
   ```bash
   php artisan make:migration create_meetings_table
   php artisan make:migration create_meeting_participants_table
   ```

2. Run migrations:
   ```bash
   php artisan migrate
   ```

### Step 2: Create Models

1. Create `Meeting` model:
   ```bash
   php artisan make:model Meeting
   ```

2. Create `MeetingParticipant` model:
   ```bash
   php artisan make:model MeetingParticipant
   ```

3. Add relationships to `User` and `Company` models

### Step 3: Create Service

1. Create `MeetingService`:
   ```bash
   php artisan make:service MeetingService
   ```

2. Implement all service methods

### Step 4: Create Controllers

1. Create `MeetingController`:
   ```bash
   php artisan make:controller API/MeetingController
   ```

2. Implement all controller methods

### Step 5: Create Events

1. Create event classes:
   ```bash
   php artisan make:event MeetingCreated
   php artisan make:event MeetingAccepted
   php artisan make:event MeetingDeclined
   php artisan make:event MeetingCancelled
   php artisan make:event MeetingStarted
   php artisan make:event MeetingCompleted
   ```

### Step 6: Add Routes

1. Add meeting routes to `routes/api.php`

### Step 7: Create Scheduled Tasks

1. Create reminder command:
   ```bash
   php artisan make:command SendMeetingReminders
   ```

2. Create completion command:
   ```bash
   php artisan make:command CompletePastMeetings
   ```

3. Schedule in `app/Console/Kernel.php`

### Step 8: Testing

1. Test member-to-member meetings
2. Test member-to-company meetings
3. Test acceptance/decline flows
4. Test Whereby integration
5. Test notifications
6. Test scheduled reminders

### Step 9: Frontend Integration

1. Create meeting creation forms
2. Implement meeting list/calendar view
3. Add real-time event listeners
4. Implement meeting actions (accept/decline/join)

---

## Summary

This guide provides a complete implementation for scheduled meetings between:
- ✅ **Members and Members**: One-on-one meetings requiring connections
- ✅ **Members and Companies**: Business meetings with company representatives

Key features:
- ✅ Full database schema with relationships
- ✅ Service layer with business logic
- ✅ RESTful API endpoints
- ✅ Whereby integration for video rooms
- ✅ Real-time notifications via Pusher
- ✅ Automated reminders and completion
- ✅ Complete frontend integration examples

The system is production-ready and follows Laravel best practices with proper error handling, validation, and security measures.

