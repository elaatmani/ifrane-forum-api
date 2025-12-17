@component('mail::message')
@isset($logoUrl)
<p style="text-align:center;"><img src="{{ $logoUrl }}" alt="{{ $appName }} Logo" style="max-height:64px;"></p>
@endisset

# {{ __('You are invited to: :title', ['title' => $meeting->title]) }}

{{ __('Hi :name,', ['name' => $recipientName]) }}

@if(!empty($meeting->description))
{{ $meeting->description }}
@endif

**{{ __('Details') }}**
- **{{ __('Organizer') }}:** {{ optional($meeting->organizer)->name }} @if(optional($meeting->organizer)->email) ({{ $meeting->organizer->email }}) @endif
- **{{ __('Scheduled at') }}:** {{ $meeting->scheduled_at->format('Y-m-d H:i') }} ({{ $meeting->timezone ?? 'UTC' }})
- **{{ __('Duration') }}:** {{ $meeting->duration_minutes ?? 60 }} {{ __('minutes') }}
@if($meeting->location)
- **{{ __('Location') }}:** {{ $meeting->location }}
@endif

{{ __('You can review the details and respond in the app.') }}

@component('mail::button', ['url' => $actionUrl])
{{ __('View / Respond') }}
@endcomponent

Thanks,<br>
{{ $appName }}
@endcomponent

