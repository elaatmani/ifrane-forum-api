<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\VideoCallRoom;
use App\Services\VideoCallService;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VideoCallRoomController extends Controller
{
    protected $videoCallService;
    protected $messagingService;

    public function __construct(VideoCallService $videoCallService, MessagingService $messagingService)
    {
        $this->videoCallService = $videoCallService;
        $this->messagingService = $messagingService;
    }

    /**
     * POST /api/video-calls/rooms
     * Create a new video call room
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Video call room creation request received', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        try {
            Log::info('Validating request data...');
            
            $validator = Validator::make($request->all(), [
                'conversation_id' => 'required|uuid|exists:conversations,id',
                'call_type' => 'required|in:video,voice'
            ]);

            if ($validator->fails()) {
                Log::warning('Video call room creation validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            Log::info('Validation passed, finding conversation...');

            $conversation = Conversation::findOrFail($request->conversation_id);
            $user = Auth::user();

            Log::info('Conversation and user found', [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, $user)) {
                Log::warning('User denied access to conversation', [
                    'user_id' => $user->id,
                    'conversation_id' => $conversation->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            Log::info('User access confirmed, initiating call...');

            // Create room and initiate call
            $result = $this->videoCallService->initiateCall(
                $conversation,
                $user,
                $request->call_type
            );

            Log::info('Video call room and call created successfully', [
                'call_id' => $result['call']->id,
                'room_id' => $result['room']->id,
                'conversation_id' => $conversation->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $result['call']->id,
                    'room_id' => $result['room']->id,
                    'participant_room_url' => $result['room']->room_url, // URL for participants to join the call
                    'whereby_meeting_id' => $result['room']->whereby_meeting_id,
                    'expires_at' => $result['room']->expires_at?->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create video call room', [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create video call room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/video-calls/rooms/{roomId}
     * Get video call room details
     */
    public function show(string $roomId): JsonResponse
    {
        try {
            $room = VideoCallRoom::with(['participants.user:id,name,profile_image'])
                                ->findOrFail($roomId);

            $user = Auth::user();

            // Check if user can access room
            if (!$this->videoCallService->canUserAccessRoom($room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'room_id' => $room->id,
                    'participant_room_url' => $room->room_url, // URL for participants to join the call
                    'conversation_id' => $room->conversation_id,
                    'call_type' => $room->call_type,
                    'status' => $room->status,
                    'participants' => $room->participants->map(function ($participant) {
                        return [
                            'user_id' => $participant->user_id,
                            'name' => $participant->user->name ?? 'Unknown',
                            'joined_at' => $participant->joined_at?->toISOString(),
                            'status' => $participant->status
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get room details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/rooms/{roomId}/join
     * Join a video call room
     */
    public function join(string $roomId): JsonResponse
    {
        try {
            $room = VideoCallRoom::findOrFail($roomId);
            $user = Auth::user();

            // Check if user can access room
            if (!$this->videoCallService->canUserAccessRoom($room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room'
                ], 403);
            }

            // Check if room is active
            if (!$room->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is not active'
                ], 400);
            }

            // Join room
            $result = $this->videoCallService->joinRoom($room, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'joined_at' => $result['joined_at']->toISOString(),
                    'participant_count' => $result['participant_count']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/rooms/{roomId}/leave
     * Leave a video call room
     */
    public function leave(string $roomId): JsonResponse
    {
        try {
            $room = VideoCallRoom::findOrFail($roomId);
            $user = Auth::user();

            // Check if user can access room
            if (!$this->videoCallService->canUserAccessRoom($room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room'
                ], 403);
            }

            // Leave room
            $result = $this->videoCallService->leaveRoom($room, $user);

            return response()->json([
                'success' => true,
                'data' => [
                    'left_at' => $result['left_at']->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/rooms/{roomId}/end
     * End a video call room
     */
    public function end(Request $request, string $roomId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'duration' => 'nullable|integer|min:0',
                'reason' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $room = VideoCallRoom::findOrFail($roomId);
            $user = Auth::user();

            // Check if user can manage room
            if (!$this->videoCallService->canUserManageRoom($room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to end this room'
                ], 403);
            }

            // End room
            $result = $this->videoCallService->endRoom(
                $room,
                $user,
                $request->get('duration'),
                $request->get('reason', 'ended_by_user')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'ended_at' => $result['ended_at']->toISOString(),
                    'duration' => $result['duration']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to end room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/video-calls/rooms/{roomId}/participants
     * Get room participants
     */
    public function participants(string $roomId): JsonResponse
    {
        try {
            $room = VideoCallRoom::findOrFail($roomId);
            $user = Auth::user();

            // Check if user can access room
            if (!$this->videoCallService->canUserAccessRoom($room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this room'
                ], 403);
            }

            // Get participants
            $participants = $this->videoCallService->getRoomParticipants($room);

            return response()->json([
                'success' => true,
                'data' => $participants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get participants: ' . $e->getMessage()
            ], 500);
        }
    }
}
