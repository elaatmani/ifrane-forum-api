<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    /**
     * Send a message to conversation
     */
    public function sendMessage(Conversation $conversation, User $sender, string $content, string $messageType = 'text', ?string $fileUrl = null, array $metadata = []): Message
    {
        DB::beginTransaction();

        try {
            $message = $this->model->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender ? $sender->id : null,
                'content' => $content,
                'message_type' => $messageType,
                'file_url' => $fileUrl,
                'metadata' => $metadata
            ]);

            // Update conversation timestamp to reflect latest activity
            $conversation->touch();

            DB::commit();
            return $message;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get conversation messages with pagination
     */
    public function getConversationMessages(Conversation $conversation, int $perPage = 50)
    {
        return $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent messages for conversation
     */
    public function getRecentMessages(Conversation $conversation, int $limit = 50)
    {
        return $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread messages count for user in conversation
     */
    public function getUnreadCount(Conversation $conversation, User $user): int
    {
        $lastRead = $conversation->users()
            ->where('user_id', $user->id)
            ->first()
            ->pivot
            ->last_read_at;

        if (!$lastRead) {
            // Count messages from other users only
            return $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->count();
        }

        // Count messages from other users created after last read
        return $conversation->messages()
            ->where('created_at', '>', $lastRead)
            ->where('sender_id', '!=', $user->id)
            ->count();
    }

    /**
     * Mark messages as read for user in conversation
     */
    public function markAsRead(Conversation $conversation, User $user): bool
    {
        try {
            $conversation->markAsRead($user);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete message (soft delete)
     */
    public function deleteMessage(Message $message): bool
    {
        try {
            return $message->delete();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get message with sender information
     */
    public function getMessageWithSender(string $messageId): ?Message
    {
        return $this->model->with('sender')->find($messageId);
    }
} 