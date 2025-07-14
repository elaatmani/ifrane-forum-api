<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Connection\ConnectionResponseRequest;
use App\Http\Resources\Connection\ConnectionRequestResource;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Config;
use Exception;

class ConnectionResponseController extends Controller
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Accept or decline a connection request.
     */
    public function __invoke(ConnectionResponseRequest $request, $connectionId)
    {
        try {
            $validatedData = $request->validated();
            $action = $validatedData['action'];
            $userId = $validatedData['user_id'];

            $connection = null;

            if ($action === 'accept') {
                $connection = $this->connectionRepository->acceptConnectionRequest($connectionId, $userId);
                $eventType = 'request_accepted';
                $successMessage = 'Connection request accepted successfully';
            } else if ($action === 'decline') {
                $connection = $this->connectionRepository->declineConnectionRequest($connectionId, $userId);
                $eventType = 'request_declined';
                $successMessage = 'Connection request declined successfully';
            }

            if (!$connection) {
                return response()->json([
                    'message' => 'Failed to process connection request',
                    'code' => 'CONNECTION_RESPONSE_FAILED'
                ], 400);
            }

            // Load relationships for the resource
            $connection->load(['sender', 'receiver']);

            // Send notification to the sender
            $this->sendConnectionNotification($connection, $eventType);

            return response()->json([
                'message' => $successMessage,
                'data' => new ConnectionRequestResource($connection),
                'code' => 'SUCCESS'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to process connection request',
                'error' => $e->getMessage(),
                'code' => 'CONNECTION_RESPONSE_ERROR'
            ], 400);
        }
    }

    /**
     * Send connection notification to the sender.
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
            [':receiver_name', ':sender_name'],
            [$connection->receiver->name, $connection->sender->name],
            $template
        );

        // Create notification for the sender
        UserNotification::create([
            'user_id' => $connection->sender_id,
            'title' => $notificationConfig['title'] ?? 'Connection Response',
            'message' => $message,
            'notification_type' => $notificationConfig['notification_type'] ?? 'connection_response',
            'severity_type' => $notificationConfig['severity'] ?? 'info',
            'data' => $connection->getNotificationData($eventType),
        ]);
    }
} 