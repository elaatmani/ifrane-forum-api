<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VideoCall;
use App\Models\Conversation;
use App\Services\VideoCallService;
use App\Services\MessagingService;
use App\Http\Resources\VideoCallResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VideoCallController extends Controller
{
    protected $videoCallService;
    protected $messagingService;

    public function __construct(VideoCallService $videoCallService, MessagingService $messagingService)
    {
        $this->videoCallService = $videoCallService;
        $this->messagingService = $messagingService;
    }

    /**
     * POST /api/video-calls/calls/initiate
     * Initiate a video call
     */
    public function initiate(Request $request): JsonResponse
    {
        Log::info('Video call initiation request received', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        try {
            Log::info('Validating request data...');
            
            $validator = Validator::make($request->all(), [
                'conversation_id' => 'required|uuid|exists:conversations,id',
                'call_type' => 'required|in:video,voice',
                'metadata' => 'nullable|array',
                'metadata.expires_at' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                Log::warning('Video call initiation validation failed', [
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
            
            Log::info('Conversation found', [
                'conversation_id' => $conversation->id,
                'conversation_type' => $conversation->type,
                'conversation_name' => $conversation->name
            ]);

            $user = Auth::user();
            
            Log::info('User authenticated', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Check if user can access conversation
            Log::info('Checking user access to conversation...');
            
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

            // Initiate call
            $result = $this->videoCallService->initiateCall(
                $conversation,
                $user,
                $request->call_type,
                $request->get('metadata', [])
            );

            Log::info('Video call initiated successfully', [
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
                    'expires_at' => $result['room']->expires_at?->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to initiate video call', [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/calls/{callId}/accept
     * Accept a video call
     */
    public function accept(string $callId): JsonResponse
    {
        Log::info('Video call accept request received', [
            'call_id' => $callId,
            'user_id' => Auth::id(),
            'call_id_length' => strlen($callId),
            'call_id_format' => 'UUID format: ' . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $callId) ? 'Valid' : 'Invalid')
        ]);

        try {
            Log::info('Looking up video call...');
            
            // First check if call exists
            $callExists = VideoCall::where('id', $callId)->exists();
            Log::info('Video call lookup result', [
                'call_id' => $callId,
                'exists_in_database' => $callExists
            ]);
            
            if (!$callExists) {
                // Log all recent calls to help debug
                $recentCalls = VideoCall::latest()->take(5)->get(['id', 'room_id', 'conversation_id', 'status', 'created_at']);
                Log::warning('Call not found, showing recent calls for debugging', [
                    'requested_call_id' => $callId,
                    'recent_calls' => $recentCalls->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Video call not found',
                    'debug_info' => [
                        'requested_call_id' => $callId,
                        'recent_calls_count' => $recentCalls->count()
                    ]
                ], 404);
            }
            
            $call = VideoCall::with(['room'])->findOrFail($callId);
            
            Log::info('Video call found', [
                'call_id' => $call->id,
                'room_id' => $call->room_id,
                'conversation_id' => $call->conversation_id,
                'status' => $call->status,
                'initiated_by' => $call->initiated_by
            ]);
            
            $user = Auth::user();
            
            Log::info('User authenticated', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Check if user can access the call
            Log::info('Checking user access to call room...');
            
            if (!$this->videoCallService->canUserAccessRoom($call->room, $user)) {
                Log::warning('User denied access to call room', [
                    'user_id' => $user->id,
                    'call_id' => $call->id,
                    'room_id' => $call->room_id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this call'
                ], 403);
            }

            // Check if call can be accepted
            Log::info('Checking if call can be accepted...');
            
            if (!$call->isInitiated() && !$call->isRinging()) {
                Log::warning('Call cannot be accepted in current state', [
                    'call_id' => $call->id,
                    'current_status' => $call->status
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Call cannot be accepted in its current state'
                ], 400);
            }

            Log::info('Call can be accepted, proceeding with acceptance...');

            // Accept call
            $result = $this->videoCallService->acceptCall($call, $user);

            Log::info('Video call accepted successfully', [
                'call_id' => $call->id,
                'accepted_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $call->id,
                    'room_id' => $call->room_id,
                    'room_url' => $call->room->room_url,
                    'accepted_at' => $result['call']->accepted_at?->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to accept video call', [
                'call_id' => $callId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/calls/{callId}/reject
     * Reject a video call
     */
    public function reject(Request $request, string $callId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'nullable|in:declined_by_user,expired,busy'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $call = VideoCall::with(['room'])->findOrFail($callId);
            $user = Auth::user();

            // Check if user can access the call
            if (!$this->videoCallService->canUserAccessRoom($call->room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this call'
                ], 403);
            }

            // Check if call can be rejected
            if (!$call->isInitiated() && !$call->isRinging()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call cannot be rejected in its current state'
                ], 400);
            }

            // Reject call
            $result = $this->videoCallService->rejectCall(
                $call,
                $user,
                $request->get('reason', 'declined_by_user')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $call->id,
                    'rejected_at' => $result['call']->rejected_at?->toISOString(),
                    'reason' => $result['call']->reject_reason
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/video-calls/calls/{callId}/end
     * End a video call
     */
    public function end(Request $request, string $callId): JsonResponse
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

            $call = VideoCall::with(['room'])->findOrFail($callId);
            $user = Auth::user();

            // Check if user can access the call
            if (!$this->videoCallService->canUserAccessRoom($call->room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this call'
                ], 403);
            }

            // Check if call can be ended
            if (!$call->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call cannot be ended in its current state'
                ], 400);
            }

            // End call
            $result = $this->videoCallService->endCall(
                $call,
                $user,
                $request->get('duration'),
                $request->get('reason', 'ended_by_user')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $call->id,
                    'ended_at' => $result['call']->ended_at?->toISOString(),
                    'duration' => $result['duration']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to end call: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/video-calls/calls/{callId}
     * Get video call details
     */
    public function show(string $callId): JsonResponse
    {
        try {
            $call = VideoCall::with(['room.participants.user:id,name,profile_image'])
                            ->findOrFail($callId);

            $user = Auth::user();

            // Check if user can access the call
            if (!$this->videoCallService->canUserAccessRoom($call->room, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this call'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $call->id,
                    'room_id' => $call->room_id,
                    'conversation_id' => $call->conversation_id,
                    'status' => $call->status,
                    'participants' => $call->room->participants->map(function ($participant) {
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
                'message' => 'Failed to get call details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/video-calls/calls/{callId}/urls
     * Get room URLs for a specific video call
     */
    public function getCallUrls(string $callId): JsonResponse
    {
        Log::info('Getting call URLs request received', [
            'call_id' => $callId,
            'user_id' => Auth::id()
        ]);

        try {
            $call = VideoCall::with(['room'])->findOrFail($callId);
            $user = Auth::user();

            // Check if user can access the call
            if (!$this->videoCallService->canUserAccessRoom($call->room, $user)) {
                Log::warning('User denied access to call URLs', [
                    'user_id' => $user->id,
                    'call_id' => $callId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this call'
                ], 403);
            }

            $isHost = $call->initiated_by === $user->id;

            Log::info('Call URLs retrieved successfully', [
                'call_id' => $call->id,
                'user_id' => $user->id,
                'is_host' => $isHost
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'call_id' => $call->id,
                    'room_id' => $call->room_id,
                    'participant_room_url' => $call->room->room_url, // URL for participants to join
                    'whereby_meeting_id' => $call->room->whereby_meeting_id,
                    'user_role' => $isHost ? 'host' : 'participant',
                    'expires_at' => $call->room->expires_at?->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get call URLs', [
                'call_id' => $callId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get call URLs: ' . $e->getMessage()
            ], 500);
        }
    }
}
