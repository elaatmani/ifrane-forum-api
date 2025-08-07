<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Send a message to conversation
     */
    public function sendMessage(Conversation $conversation, User $sender, string $content, string $messageType = 'text', ?string $fileUrl = null, array $metadata = []): Message;

    /**
     * Get conversation messages with pagination
     */
    public function getConversationMessages(Conversation $conversation, int $perPage = 50);

    /**
     * Get recent messages for conversation
     */
    public function getRecentMessages(Conversation $conversation, int $limit = 50);

    /**
     * Get unread messages count for user in conversation
     */
    public function getUnreadCount(Conversation $conversation, User $user): int;

    /**
     * Mark messages as read for user in conversation
     */
    public function markAsRead(Conversation $conversation, User $user): bool;

    /**
     * Delete message (soft delete)
     */
    public function deleteMessage(Message $message): bool;

    /**
     * Get message with sender information
     */
    public function getMessageWithSender(string $messageId): ?Message;
} 