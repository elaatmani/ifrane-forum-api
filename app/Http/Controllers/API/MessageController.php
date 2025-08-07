<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessagingService;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class MessageController extends Controller
{
    public $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Send text message to conversation
     */
    public function sendTextMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendTextMessage(
                $conversation,
                auth()->user(),
                $request->content
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Message sent successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send file message to conversation
     */
    public function sendFileMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'content' => 'nullable|string|max:500'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendFileMessage(
                $conversation,
                auth()->user(),
                $request->file('file'),
                $request->get('content', '')
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'File sent successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send missed call message
     */
    public function sendMissedCallMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendMissedCallMessage(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Missed call message sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send video call request
     */
    public function sendVideoCallRequest(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendVideoCallRequest(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Video call request sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send voice call request
     */
    public function sendVoiceCallRequest(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendVoiceCallRequest(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Voice call request sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send call ended message
     */
    public function sendCallEndedMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendCallEndedMessage(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Call ended message sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send call rejected message
     */
    public function sendCallRejectedMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendCallRejectedMessage(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Call rejected message sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send call accepted message
     */
    public function sendCallAcceptedMessage(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'metadata' => 'nullable|array'
            ]);

            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $message = $this->messagingService->sendCallAcceptedMessage(
                $conversation,
                auth()->user(),
                $request->get('metadata', [])
            );

            return response()->json([
                'success' => true,
                'data' => new MessageResource($message),
                'message' => 'Call accepted message sent'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete message
     */
    public function deleteMessage(Message $message): JsonResponse
    {
        try {
            // Check if user is the sender
            if ($message->sender_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own messages'
                ], 403);
            }

            $this->messagingService->deleteMessage($message);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get unread count for conversation
     */
    public function getUnreadCount(Conversation $conversation): JsonResponse
    {
        try {
            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $unreadCount = $this->messagingService->getUnreadCount($conversation, auth()->user());

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 