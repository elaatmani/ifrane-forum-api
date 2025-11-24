<?php

namespace App\Services;

use App\Models\VideoCallRoom;
use App\Models\VideoCall;
use App\Models\VideoCallParticipant;
use App\Models\Conversation;
use App\Models\User;
use App\Events\VideoCallInitiated;
use App\Events\VideoCallAccepted;
use App\Events\VideoCallRejected;
use App\Events\VideoCallEnded;
use App\Events\VideoCallParticipantJoined;
use App\Events\VideoCallParticipantLeft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VideoCallService
{
    protected $wherebyService;

    public function __construct(WherebyService $wherebyService)
    {
        $this->wherebyService = $wherebyService;
    }

    /**
     * Create a new video call room
     */
    public function createRoom(Conversation $conversation, User $creator, string $callType, array $metadata = [])
    {
        Log::info('Starting to create video call room', [
            'conversation_id' => $conversation->id,
            'creator_id' => $creator->id,
            'call_type' => $callType,
            'metadata' => $metadata
        ]);

        DB::beginTransaction();

        try {
            Log::info('Creating Whereby meeting...');
            
            // Create Whereby meeting
            $wherebyResult = $this->wherebyService->createMeeting([
                'roomNamePrefix' => 'foodshow',
                'roomNamePattern' => 'human-short',
                'endDate' => $metadata['expires_at'] ?? now()->addHours(24)->toISOString()
            ]);

            Log::info('Whereby meeting creation result', [
                'success' => $wherebyResult['success'],
                'meeting_id' => $wherebyResult['meeting_id'] ?? null,
                'room_url' => $wherebyResult['room_url'] ?? null,
                'whereby_result_keys' => array_keys($wherebyResult),
                'whereby_result_full' => $wherebyResult
            ]);

            if (!$wherebyResult['success']) {
                throw new \Exception('Failed to create Whereby meeting');
            }

            // Log the complete Whereby data flow
            Log::info('Whereby data flow analysis', [
                'whereby_meeting_id' => $wherebyResult['meeting_id'],
                'whereby_room_url' => $wherebyResult['room_url'],
                'whereby_host_room_url' => $wherebyResult['host_room_url'] ?? 'NOT_SET',
                'whereby_room_name' => $wherebyResult['room_name'] ?? 'NOT_SET',
                'raw_whereby_data' => $wherebyResult['raw_whereby_data'] ?? 'NOT_SET',
                'data_source' => isset($wherebyResult['raw_whereby_data']) ? 'Real Whereby API' : 'Mock Meeting'
            ]);

            Log::info('Creating VideoCallRoom in database...');

            // Create room
            $room = VideoCallRoom::create([
                'conversation_id' => $conversation->id,
                'whereby_meeting_id' => $wherebyResult['meeting_id'],
                'room_url' => $wherebyResult['room_url'], // Main room URL for full participant access (camera/mic)
                'host_room_url' => $wherebyResult['host_room_url'],
                'call_type' => $callType,
                'status' => 'active',
                'created_by' => $creator->id,
                'expires_at' => $metadata['expires_at'] ?? now()->addMinutes(30)
            ]);

            Log::info('VideoCallRoom created successfully', [
                'room_id' => $room->id,
                'conversation_id' => $room->conversation_id,
                'whereby_meeting_id' => $room->whereby_meeting_id,
                'room_url' => $room->room_url,
                'host_room_url' => $room->host_room_url,
                'url_structure' => [
                    'participant_url' => $room->room_url, // Full participant access with camera/mic
                    'host_url' => $room->host_room_url, // Host with admin controls
                    'whereby_meeting_id' => $room->whereby_meeting_id
                ]
            ]);

            Log::info('Adding creator as first participant...');

            // Add creator as first participant
            $room->addParticipant($creator->id);
            $room->markParticipantJoined($creator->id);

            Log::info('Creator added as participant successfully');

            DB::commit();

            Log::info('Video call room creation completed successfully', [
                'room_id' => $room->id,
                'conversation_id' => $room->conversation_id
            ]);

            return [
                'success' => true,
                'room' => $room,
                'whereby_data' => $wherebyResult
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create video call room', [
                'conversation_id' => $conversation->id,
                'creator_id' => $creator->id,
                'call_type' => $callType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Initiate a video call
     */
    public function initiateCall(Conversation $conversation, User $initiator, string $callType, array $metadata = [])
    {
        Log::info('Starting to initiate video call', [
            'conversation_id' => $conversation->id,
            'initiator_id' => $initiator->id,
            'call_type' => $callType,
            'metadata' => $metadata
        ]);

        DB::beginTransaction();

        try {
            Log::info('Creating video call room...');
            
            // Create room first
            $roomResult = $this->createRoom($conversation, $initiator, $callType, $metadata);
            $room = $roomResult['room'];

            Log::info('createRoom completed, room result received', [
                'room_id' => $room->id,
                'room_result_keys' => array_keys($roomResult),
                'room_result_success' => $roomResult['success'] ?? 'unknown'
            ]);

            Log::info('Room created successfully, now creating VideoCall record...', [
                'room_id' => $room->id,
                'room_result_keys' => array_keys($roomResult)
            ]);

            // Create call record
            $callData = [
                'room_id' => $room->id,
                'conversation_id' => $conversation->id,
                'call_type' => $callType,
                'status' => 'initiated',
                'initiated_by' => $initiator->id
            ];

            Log::info('Attempting to create VideoCall with data', $callData);

            try {
                $call = VideoCall::create($callData);
                Log::info('VideoCall::create() executed successfully');
            } catch (\Exception $e) {
                Log::error('VideoCall::create() failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'call_data' => $callData
                ]);
                throw $e;
            }

            Log::info('VideoCall record created successfully', [
                'call_id' => $call->id,
                'room_id' => $call->room_id,
                'conversation_id' => $call->conversation_id,
                'status' => $call->status,
                'created_at' => $call->created_at
            ]);

            // Add all conversation participants as invited (if any exist)
            try {
                Log::info('Attempting to add conversation participants...');
                
                $conversationUsers = $conversation->users;
                Log::info('Conversation users loaded', [
                    'conversation_id' => $conversation->id,
                    'users_count' => $conversationUsers ? $conversationUsers->count() : 'null',
                    'users_type' => $conversationUsers ? get_class($conversationUsers) : 'null'
                ]);

                if ($conversationUsers && $conversationUsers->count() > 0) {
                    Log::info('Adding participants to video call room...');
                    
                    foreach ($conversationUsers as $user) {
                        if ($user->id !== $initiator->id) {
                            Log::info('Adding participant to room', [
                                'user_id' => $user->id,
                                'room_id' => $room->id
                            ]);
                            
                            $room->addParticipant($user->id);
                        }
                    }
                    
                    Log::info('All participants added successfully');
                } else {
                    Log::info('No conversation participants found, skipping participant addition');
                }
            } catch (\Exception $e) {
                // Log the error but don't fail the call creation
                Log::warning('Failed to add conversation participants to video call room', [
                    'conversation_id' => $conversation->id,
                    'room_id' => $room->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            Log::info('About to commit database transaction...');

            DB::commit();

            Log::info('Video call initiation completed successfully, broadcasting event...');

            // Broadcast call initiated event
            event(new VideoCallInitiated($call));

            Log::info('VideoCallInitiated event broadcasted successfully');

            return [
                'success' => true,
                'call' => $call,
                'room' => $room
            ];

        } catch (\Exception $e) {
            Log::error('Failed to initiate video call', [
                'conversation_id' => $conversation->id,
                'initiator_id' => $initiator->id,
                'call_type' => $callType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            DB::rollback();
            throw $e;
        }
    }

    /**
     * Accept a video call
     */
    public function acceptCall(VideoCall $call, User $accepter)
    {
        DB::beginTransaction();

        try {
            // Update call status
            $call->accept($accepter->id);

            // Update room participant status
            $call->room->markParticipantJoined($accepter->id);

            // Update call status to accepted
            $call->update(['status' => 'accepted']);

            DB::commit();

            // Broadcast call accepted event
            event(new VideoCallAccepted($call));

            return [
                'success' => true,
                'call' => $call
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to accept video call', [
                'call_id' => $call->id,
                'accepter_id' => $accepter->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Reject a video call
     */
    public function rejectCall(VideoCall $call, User $rejecter, string $reason = 'declined_by_user')
    {
        DB::beginTransaction();

        try {
            // Update call status
            $call->reject($rejecter->id, $reason);

            // Mark participant as left
            $call->room->markParticipantLeft($rejecter->id);

            DB::commit();

            // Broadcast call rejected event
            event(new VideoCallRejected($call));

            return [
                'success' => true,
                'call' => $call
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reject video call', [
                'call_id' => $call->id,
                'rejecter_id' => $rejecter->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * End a video call
     */
    public function endCall(VideoCall $call, User $ender, int $duration = null, string $reason = 'ended_by_user')
    {
        DB::beginTransaction();

        try {
            // Calculate duration if not provided
            if ($duration === null && $call->accepted_at) {
                $duration = $call->accepted_at->diffInSeconds(now());
            }

            // End the call
            $call->end($duration, $reason);

            // End the room
            $call->room->endRoom($reason);

            DB::commit();

            // Broadcast call ended event
            event(new VideoCallEnded($call));

            return [
                'success' => true,
                'call' => $call,
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to end video call', [
                'call_id' => $call->id,
                'ender_id' => $ender->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Join a video call room
     */
    public function joinRoom(VideoCallRoom $room, User $user)
    {
        try {
            // Check if user is already a participant
            if (!$room->hasParticipant($user->id)) {
                $room->addParticipant($user->id);
            }

            // Mark as joined
            $room->markParticipantJoined($user->id);

            // Broadcast participant joined event
            event(new VideoCallParticipantJoined($room, $user));

            return [
                'success' => true,
                'joined_at' => now(),
                'participant_count' => $room->getParticipantCount()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to join video call room', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Leave a video call room
     */
    public function leaveRoom(VideoCallRoom $room, User $user)
    {
        try {
            // Mark participant as left
            $room->markParticipantLeft($user->id);

            // Broadcast participant left event
            event(new VideoCallParticipantLeft($room, $user));

            return [
                'success' => true,
                'left_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to leave video call room', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * End a video call room
     */
    public function endRoom(VideoCallRoom $room, User $ender, int $duration = null, string $reason = 'ended_by_user')
    {
        DB::beginTransaction();

        try {
            // End all active calls in the room
            foreach ($room->videoCalls()->active()->get() as $call) {
                if ($duration === null && $call->accepted_at) {
                    $duration = $call->accepted_at->diffInSeconds(now());
                }
                $call->end($duration, $reason);
            }

            // End the room
            $room->endRoom($reason);

            DB::commit();

            return [
                'success' => true,
                'ended_at' => now(),
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to end video call room', [
                'room_id' => $room->id,
                'ender_id' => $ender->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get room participants
     */
    public function getRoomParticipants(VideoCallRoom $room)
    {
        return $room->participants()
                    ->with('user:id,name,profile_image')
                    ->get()
                    ->map(function ($participant) {
                        return [
                            'user_id' => $participant->user->id,
                            'name' => $participant->user->name,
                            'joined_at' => $participant->joined_at,
                            'status' => $participant->status
                        ];
                    });
    }

    /**
     * Check if user can access room
     */
    public function canUserAccessRoom(VideoCallRoom $room, User $user)
    {
        // Check if user is participant in the conversation
        return $room->conversation->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user can manage room
     */
    public function canUserManageRoom(VideoCallRoom $room, User $user)
    {
        // Room creator can manage the room
        return $room->created_by === $user->id;
    }

    /**
     * Auto-expire expired calls
     */
    public function expireExpiredCalls()
    {
        $expiredCalls = VideoCall::whereIn('status', ['initiated', 'ringing'])
                                ->where('created_at', '<', now()->subSeconds(60))
                                ->get();

        foreach ($expiredCalls as $call) {
            try {
                $call->markAsMissed('expired');
                
                // End the room if no active calls
                if (!$call->room->videoCalls()->active()->exists()) {
                    $call->room->endRoom('expired');
                }
            } catch (\Exception $e) {
                Log::error('Failed to expire call', [
                    'call_id' => $call->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $expiredCalls->count();
    }

    /**
     * Clean up expired rooms
     */
    public function cleanupExpiredRooms()
    {
        $expiredRooms = VideoCallRoom::active()
                                    ->where('expires_at', '<', now())
                                    ->get();

        foreach ($expiredRooms as $room) {
            try {
                $room->endRoom('expired');
            } catch (\Exception $e) {
                Log::error('Failed to cleanup expired room', [
                    'room_id' => $room->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $expiredRooms->count();
    }
}

