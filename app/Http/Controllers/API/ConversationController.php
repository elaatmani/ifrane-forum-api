<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Session;
use App\Models\Company;
use App\Services\MessagingService;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ConversationController extends Controller
{
    public $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Get user's conversations by type
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'direct');
            $perPage = $request->get('per_page', 20);
            
            $conversations = $this->messagingService->getUserConversations(
                auth()->user(), 
                $type, 
                $perPage
            );

            return response()->json([
                'success' => true,
                'data' => ConversationResource::collection($conversations),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get direct conversation with specific user
     */
    public function getDirectConversation(User $user): JsonResponse
    {
        try {
            $conversation = $this->messagingService->startDirectConversation(
                auth()->user(), 
                $user
            );

            return response()->json([
                'success' => true,
                'data' => new ConversationResource($conversation)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get session conversation
     */
    public function getSessionConversation(Session $session): JsonResponse
    {
        try {
            $conversation = $this->messagingService->getSessionConversation($session);

            // Check if user is in session
            if (!$session->users()->where('user_id', auth()->id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this session'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => new ConversationResource($conversation)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get company conversation
     */
    public function getCompanyConversation(Company $company): JsonResponse
    {
        try {
            $conversation = $this->messagingService->getCompanyConversation(
                auth()->user(), 
                $company
            );

            return response()->json([
                'success' => true,
                'data' => new ConversationResource($conversation)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get conversation messages
     */
    public function getMessages(Conversation $conversation, Request $request): JsonResponse
    {
        try {
            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $perPage = $request->get('per_page', 50);
            $messages = $this->messagingService->getConversationMessages($conversation, $perPage);

            return response()->json([
                'success' => true,
                'data' => MessageResource::collection($messages),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(Conversation $conversation): JsonResponse
    {
        try {
            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $this->messagingService->markConversationAsRead($conversation, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Conversation marked as read'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get conversation participants
     */
    public function getParticipants(Conversation $conversation): JsonResponse
    {
        try {
            // Check if user can access conversation
            if (!$this->messagingService->canUserAccessConversation($conversation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this conversation'
                ], 403);
            }

            $participants = $this->messagingService->getConversationParticipants($conversation);

            return response()->json([
                'success' => true,
                'data' => $participants
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
                    'conversation_id' => $conversation->id,
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