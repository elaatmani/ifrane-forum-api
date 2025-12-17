<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
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
        return $this->subject(__('Reset your :app password', ['app' => config('mail-branding.app_name')]))
            ->markdown('emails.password_reset')
            ->with([
                'name' => $this->name,
                'resetUrl' => $this->resetUrl,
                'appName' => config('mail-branding.app_name'),
                'supportEmail' => config('mail-branding.support_email'),
                'logoUrl' => config('mail-branding.logo_url'),
            ]);
    }
}

