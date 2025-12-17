<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $resetUrl;

    public function __construct(string $name, string $resetUrl)
    {
        $this->name = $name;
        $this->resetUrl = $resetUrl;
    }

    public function build(): self
    {
        return $this->subject(__('Welcome to :app', ['app' => config('mail-branding.app_name')]))
            ->markdown('emails.welcome')
            ->with([
                'name' => $this->name,
                'resetUrl' => $this->resetUrl,
                'appName' => config('mail-branding.app_name'),
                'supportEmail' => config('mail-branding.support_email'),
                'logoUrl' => config('mail-branding.logo_url'),
            ]);
    }
}

