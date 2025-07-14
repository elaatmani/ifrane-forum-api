<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Connection\ConnectionRequestStoreRequest;
use App\Http\Resources\Connection\ConnectionRequestResource;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Config;
use Exception;

class ConnectionRequestController extends Controller
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Send a connection request to another user.
     */
    public function __invoke(ConnectionRequestStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            // Send the connection request
            $connection = $this->connectionRepository->sendConnectionRequest(
                $validatedData['sender_id'],
                $validatedData['receiver_id'],
                $validatedData['message']
            );

            if (!$connection) {
                return response()->json([
                    'message' => 'Failed to send connection request',
                    'code' => 'CONNECTION_REQUEST_FAILED'
                ], 400);
            }

            // Load relationships for the resource
            $connection->load(['sender', 'receiver']);

            // Send notification if enabled
            $this->sendConnectionNotification($connection, 'request_sent');

            return response()->json([
                'message' => 'Connection request sent successfully',
                'data' => new ConnectionRequestResource($connection),
                'code' => 'SUCCESS'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to send connection request',
                'error' => $e->getMessage(),
                'code' => 'CONNECTION_REQUEST_ERROR'
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
            [':sender_name', ':message'],
            [$connection->sender->name, $connection->message],
            $template
        );

        // Create notification for the receiver
        UserNotification::create([
            'user_id' => $connection->receiver_id,
            'title' => $notificationConfig['title'] ?? 'Connection Request',
            'message' => $message,
            'notification_type' => $notificationConfig['notification_type'] ?? 'connection_request',
            'severity_type' => $notificationConfig['severity'] ?? 'info',
            'data' => $connection->getNotificationData($eventType),
        ]);
    }
} 