<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Meeting $meeting;
    public string $recipientName;
    public string $joinUrl;

    public function __construct(Meeting $meeting, string $recipientName, string $joinUrl)
    {
        $this->meeting = $meeting;
        $this->recipientName = $recipientName;
        $this->joinUrl = $joinUrl;
    }

    public function build(): self
    {
        $this->meeting->loadMissing('organizer');

        return $this->subject(__('Reminder: :title', ['title' => $this->meeting->title]))
            ->markdown('emails.meeting_reminder')
            ->with([
                'meeting' => $this->meeting,
                'recipientName' => $this->recipientName,
                'joinUrl' => $this->joinUrl,
                'appName' => config('mail-branding.app_name'),
                'supportEmail' => config('mail-branding.support_email'),
                'logoUrl' => config('mail-branding.logo_url'),
            ]);
    }
}

