<?php

namespace App\Mail;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Meeting $meeting;
    public string $recipientName;
    public string $actionUrl;

    public function __construct(Meeting $meeting, string $recipientName, string $actionUrl)
    {
        $this->meeting = $meeting;
        $this->recipientName = $recipientName;
        $this->actionUrl = $actionUrl;
    }

    public function build(): self
    {
        $this->meeting->loadMissing('organizer');

        return $this->subject(__('Meeting invitation: :title', ['title' => $this->meeting->title]))
            ->markdown('emails.meeting_invitation')
            ->with([
                'meeting' => $this->meeting,
                'recipientName' => $this->recipientName,
                'actionUrl' => $this->actionUrl,
                'appName' => config('mail-branding.app_name'),
                'supportEmail' => config('mail-branding.support_email'),
                'logoUrl' => config('mail-branding.logo_url'),
            ]);
    }
}

