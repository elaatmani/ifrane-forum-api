<?php

return [
    'app_name' => env('MAIL_BRAND_APP_NAME', config('app.name')),
    'support_email' => env('MAIL_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS')),
    'logo_url' => env('MAIL_LOGO_URL'),
    'primary_color' => env('MAIL_PRIMARY_COLOR', '#0d6efd'),
    'secondary_color' => env('MAIL_SECONDARY_COLOR', '#6c757d'),
    'send_welcome' => env('MAIL_SEND_WELCOME', false),
];

