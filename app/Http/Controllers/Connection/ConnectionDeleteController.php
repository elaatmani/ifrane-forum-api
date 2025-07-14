<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Exception;

class ConnectionDeleteController extends Controller
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Remove/Delete a connection.
     */
    public function __invoke(Request $request, $connectionId)
    {
        try {
            $userId = auth()->id();

            // Get the connection before deleting for notification purposes
            $connection = $this->connectionRepository->getConnectionBetweenUsers($userId, $connectionId);
            
            if (!$connection || (!$connection->isSentBy($userId) && !$connection->isReceivedBy($userId))) {
                return response()->json([
                    'message' => 'Connection not found or you are not authorized to remove it',
                    'code' => 'CONNECTION_NOT_FOUND'
                ], 404);
            }

            // Store the other user's ID for notification
            $otherUserId = $connection->getOtherUser($userId)->id;
            $otherUser = $connection->getOtherUser($userId);

            $result = $this->connectionRepository->removeConnection($connectionId, $userId);

            if (!$result) {
                return response()->json([
                    'message' => 'Failed to remove connection',
                    'code' => 'CONNECTION_DELETE_FAILED'
                ], 400);
            }

            // Send notification to the other user
            $this->sendConnectionNotification($connection, $otherUserId, 'connection_removed');

            return response()->json([
                'message' => 'Connection removed successfully',
                'code' => 'SUCCESS'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to remove connection',
                'error' => $e->getMessage(),
                'code' => 'CONNECTION_DELETE_ERROR'
            ], 400);
        }
    }

    /**
     * Send connection notification to the other user.
     */
    private function sendConnectionNotification($connection, $otherUserId, $eventType)
    {
        // Check if notifications are enabled for this event
        if (!$connection->shouldSendNotification($eventType)) {
            return;
        }

        $notificationConfig = Config::get('connections.notifications.events.' . $eventType, []);
        
        // Get the notification template and replace placeholders
        $template = $notificationConfig['message_template'] ?? '';
        $currentUser = auth()->user();
        $message = str_replace(
            [':user_name'],
            [$currentUser->name],
            $template
        );

        // Create notification for the other user
        UserNotification::create([
            'user_id' => $otherUserId,
            'title' => $notificationConfig['title'] ?? 'Connection Removed',
            'message' => $message,
            'notification_type' => $notificationConfig['notification_type'] ?? 'connection_removed',
            'severity_type' => $notificationConfig['severity'] ?? 'info',
            'data' => [
                'connection_id' => $connection->id,
                'event_type' => $eventType,
                'removed_by' => [
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                    'email' => $currentUser->email,
                ],
                'removed_at' => now(),
            ],
        ]);
    }
} 