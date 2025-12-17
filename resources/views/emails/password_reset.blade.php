@component('mail::message')
@isset($logoUrl)
<p style="text-align:center;"><img src="{{ $logoUrl }}" alt="{{ $appName }} Logo" style="max-height:64px;"></p>
@endisset

# {{ __('Hi :name,', ['name' => $name]) }}

{{ __('You requested to reset your password. Click the button below to continue.') }}

@component('mail::button', ['url' => $resetUrl])
{{ __('Reset Password') }}
@endcomponent

{{ __('If you did not request this, please ignore this email.') }}

Thanks,<br>
{{ $appName }}
@endcomponent

