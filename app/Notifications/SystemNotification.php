<?php

namespace App\Notifications;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SystemNotification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $message;
    protected $data;
    protected $severityType;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, array $data = [], string $severityType = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->severityType = $severityType;
    }

    /**
     * Create the UserNotification record.
     */
    public function send($user)
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $this->title,
            'message' => $this->message,
            'notification_type' => 'system',
            'severity_type' => $this->severityType,
            'data' => $this->data,
        ]);
    }

    /**
     * Send this notification to multiple users.
     */
    public function broadcastToUsers($users)
    {
        foreach ($users as $user) {
            $this->send($user);
        }
    }
} 