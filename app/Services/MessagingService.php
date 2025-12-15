<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Session;
use App\Models\Company;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Events\MessageSent;
use App\Events\ConversationCreated;
use App\Events\ConversationUpdated;
use App\Events\UnreadCountUpdated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class MessagingService
{
    protected $conversationRepository;
    protected $messageRepository;

    public function __construct(
        ConversationRepositoryInterface $conversationRepository,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Start a direct conversation between two users
     */
    public function startDirectConversation(User $user1, User $user2): Conversation
    {
        $conversation = $this->conversationRepository->getOrCreateDirectConversation($user1, $user2);
        
        // If this is a newly created conversation, broadcast the event
        if ($conversation->wasRecentlyCreated) {
            event(new ConversationCreated($conversation));
        }
        
        return $conversation;
    }

    /**
     * Get or create session conversation
     */
    public function getSessionConversation(Session $session): Conversation
    {
        $conversation = $this->conversationRepository->getOrCreateSessionConversation($session);
        
        // If this is a newly created conversation, broadcast the event
        if ($conversation->wasRecentlyCreated) {
            event(new ConversationCreated($conversation));
        }
        
        return $conversation;
    }

    /**
     * Get or create company conversation
     */
    public function getCompanyConversation(User $user, Company $company): Conversation
    {
        $conversation = $this->conversationRepository->getOrCreateCompanyConversation($user, $company);
        
        // If this is a newly created conversation, broadcast the event
        if ($conversation->wasRecentlyCreated) {
            event(new ConversationCreated($conversation));
        }
        
        return $conversation;
    }

    /**
     * Send text message
     */
    public function sendTextMessage(Conversation $conversation, User $sender, string $content): Message
    {
        $message = $this->messageRepository->sendMessage($conversation, $sender, $content, 'text');
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        // Broadcast message event
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send missed call message
     */
    public function sendMissedCallMessage(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Missed call', 
            'missed_call',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send video call request message
     */
    public function sendVideoCallRequest(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Video call request', 
            'video_call_request',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send voice call request message
     */
    public function sendVoiceCallRequest(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Voice call request', 
            'voice_call_request',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send call ended message
     */
    public function sendCallEndedMessage(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Call ended', 
            'call_ended',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send call rejected message
     */
    public function sendCallRejectedMessage(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Call rejected', 
            'call_rejected',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send call accepted message
     */
    public function sendCallAcceptedMessage(Conversation $conversation, User $sender, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            $sender, 
            'Call accepted', 
            'call_accepted',
            null,
            $metadata
        );
        
        // Mark conversation as read for the sender (since they sent the last message)
        $this->messageRepository->markAsRead($conversation, $sender);
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        // Broadcast unread count updates to all participants (including sender with 0 count)
        $this->broadcastUnreadCountUpdates($conversation, $sender);
        
        return $message;
    }

    /**
     * Send system message
     */
    public function sendSystemMessage(Conversation $conversation, string $content, array $metadata = []): Message
    {
        $message = $this->messageRepository->sendMessage(
            $conversation, 
            null, // System messages don't have a sender
            $content, 
            'system',
            null,
            $metadata
        );
        
        event(new MessageSent($message));
        
        // Broadcast conversation update
        event(new ConversationUpdated($conversation));
        
        return $message;
    }

    /**
     * Send file message
     */
    public function sendFileMessage(Conversation $conversation, User $sender, UploadedFile $file, string $content = ''): Message
    {
        try {
            // Store file
            $filePath = $file->store('messages/files', 'public');
            
            $message = $this->messageRepository->sendMessage(
                $conversation, 
                $sender, 
                $content ?: $file->getClientOriginalName(), 
                'file', 
                $filePath
            );
            
            // Mark conversation as read for the sender (since they sent the last message)
            $this->messageRepository->markAsRead($conversation, $sender);
            
            // Broadcast message event
            event(new MessageSent($message));
            
            // Broadcast conversation update
            event(new ConversationUpdated($conversation));
            
            // Broadcast unread count updates to all participants (including sender with 0 count)
            $this->broadcastUnreadCountUpdates($conversation, $sender);
            
            return $message;
            
        } catch (Exception $e) {
            throw new Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Get user's conversations by type
     */
    public function getUserConversations(User $user, string $type = 'direct', int $perPage = 20)
    {
        switch ($type) {
            case 'direct':
                return $this->conversationRepository->getUserDirectConversations($user, $perPage);
            case 'session':
                return $this->conversationRepository->getUserSessionConversations($user, $perPage);
            case 'company':
                return $this->conversationRepository->getUserCompanyConversations($user, $perPage);
            default:
                throw new Exception('Invalid conversation type');
        }
    }

    /**
     * Get conversation messages
     */
    public function getConversationMessages(Conversation $conversation, int $perPage = 50)
    {
        return $this->messageRepository->getConversationMessages($conversation, $perPage);
    }

    /**
     * Mark conversation as read
     */
    public function markConversationAsRead(Conversation $conversation, User $user): bool
    {
        $result = $this->messageRepository->markAsRead($conversation, $user);
        
        if ($result) {
            // Get updated unread count for this conversation
            $unreadCount = $this->getUnreadCount($conversation, $user);
            
            // Get total unread count across all conversations
            $totalUnreadCount = $this->getTotalUnreadCount($user);
            
            // Broadcast unread count update with total count
            event(new UnreadCountUpdated($user, $unreadCount, $conversation->id, $totalUnreadCount));
            
            // Broadcast conversation update
            event(new ConversationUpdated($conversation));
        }
        
        return $result;
    }

    /**
     * Get unread count for conversation
     */
    public function getUnreadCount(Conversation $conversation, User $user): int
    {
        return $this->messageRepository->getUnreadCount($conversation, $user);
    }

    /**
     * Get total unread messages count across all conversations for a user
     */
    public function getTotalUnreadCount(User $user): int
    {
        $totalUnread = 0;
        
        // Get all conversations where user is a participant
        $conversations = $user->conversations()->get();
        
        // Sum unread counts from all conversations
        foreach ($conversations as $conversation) {
            $totalUnread += $this->getUnreadCount($conversation, $user);
        }
        
        return $totalUnread;
    }

    /**
     * Delete message
     */
    public function deleteMessage(Message $message): bool
    {
        // Delete file if exists
        if ($message->isFile() && $message->file_url) {
            Storage::disk('public')->delete($message->file_url);
        }
        
        return $this->messageRepository->deleteMessage($message);
    }

    /**
     * Add user to session conversation when they join session
     */
    public function addUserToSessionChat(Session $session, User $user): bool
    {
        $conversation = $this->getSessionConversation($session);
        return $this->conversationRepository->addUserToConversation($conversation, $user);
    }

    /**
     * Remove user from session conversation when they leave session
     */
    public function removeUserFromSessionChat(Session $session, User $user): bool
    {
        $conversation = $this->getSessionConversation($session);
        return $this->conversationRepository->removeUserFromConversation($conversation, $user);
    }

    /**
     * Get conversation participants
     */
    public function getConversationParticipants(Conversation $conversation)
    {
        return $this->conversationRepository->getConversationParticipants($conversation);
    }

    /**
     * Check if user can access conversation
     */
    public function canUserAccessConversation(Conversation $conversation, User $user): bool
    {
        return $this->conversationRepository->isUserInConversation($conversation, $user);
    }

    /**
     * Broadcast unread count updates to all participants of a conversation (including sender)
     */
    protected function broadcastUnreadCountUpdates(Conversation $conversation, User $sender): void
    {
        // Load conversation users
        $conversation->load('users');
        
        // Broadcast to all participants (including sender)
        foreach ($conversation->users as $participant) {
            // Get unread count for this conversation
            $unreadCount = $this->getUnreadCount($conversation, $participant);
            
            // Get total unread count across all conversations
            $totalUnreadCount = $this->getTotalUnreadCount($participant);
            
            // Broadcast unread count update with total count
            event(new UnreadCountUpdated($participant, $unreadCount, $conversation->id, $totalUnreadCount));
        }
    }
} 