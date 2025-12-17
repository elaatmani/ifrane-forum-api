@component('mail::message')
@isset($logoUrl)
<p style="text-align:center;"><img src="{{ $logoUrl }}" alt="{{ $appName }} Logo" style="max-height:64px;"></p>
@endisset

# {{ __('Welcome, :name!', ['name' => $name]) }}

{{ __('We\'re excited to have you at :app.', ['app' => $appName]) }}

@component('mail::button', ['url' => $resetUrl])
{{ __('Get Started') }}
@endcomponent

{{ __('If you have questions, reply to this email or contact :email.', ['email' => $supportEmail]) }}

Thanks,<br>
{{ $appName }}
@endcomponent

