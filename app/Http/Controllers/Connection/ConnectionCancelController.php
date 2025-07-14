<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Controller;
use App\Http\Resources\Connection\ConnectionRequestResource;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Exception;

class ConnectionCancelController extends Controller
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Cancel a connection request.
     */
    public function __invoke(Request $request, $connectionId)
    {
        try {
            $userId = auth()->id();

            $connection = $this->connectionRepository->cancelConnectionRequest($connectionId, $userId);

            if (!$connection) {
                return response()->json([
                    'message' => 'Connection request not found or cannot be cancelled',
                    'code' => 'CONNECTION_CANCEL_FAILED'
                ], 400);
            }

            // Load relationships for the resource
            $connection->load(['sender', 'receiver']);

            // Send notification to the receiver
            $this->sendConnectionNotification($connection, 'request_cancelled');

            return response()->json([
                'message' => 'Connection request cancelled successfully',
                'data' => new ConnectionRequestResource($connection),
                'code' => 'SUCCESS'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel connection request',
                'error' => $e->getMessage(),
                'code' => 'CONNECTION_CANCEL_ERROR'
            ], 400);
        }
    }

    /**
     * Send connection notification to the receiver.
     */
    private function sendConnectionNotification($connection, $eventType)
    {
        // Check if notifications are enabled for this event
        if (!$connection->shouldSendNotification($eventType)) {
            return;
        }

        $notificationConfig = Config::get('connections.notifications.events.' . $eventType, []);
        
        // Get the notification template and replace placeholders
        $template = $notificationConfig['message_template'] ?? '';
        $message = str_replace(
            [':sender_name', ':receiver_name'],
            [$connection->sender->name, $connection->receiver->name],
            $template
        );

        // Create notification for the receiver
        UserNotification::create([
            'user_id' => $connection->receiver_id,
            'title' => $notificationConfig['title'] ?? 'Connection Cancelled',
            'message' => $message,
            'notification_type' => $notificationConfig['notification_type'] ?? 'connection_cancelled',
            'severity_type' => $notificationConfig['severity'] ?? 'info',
            'data' => $connection->getNotificationData($eventType),
        ]);
    }
} 