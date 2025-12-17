@component('mail::message')
@isset($logoUrl)
<p style="text-align:center;"><img src="{{ $logoUrl }}" alt="{{ $appName }} Logo" style="max-height:64px;"></p>
@endisset

# {{ __('Reminder: :title', ['title' => $meeting->title]) }}

{{ __('Hi :name,', ['name' => $recipientName]) }}

{{ __('Your meeting is coming up.') }}

**{{ __('Details') }}**
- **{{ __('Title') }}:** {{ $meeting->title }}
- **{{ __('Organizer') }}:** {{ optional($meeting->organizer)->name }} @if(optional($meeting->organizer)->email) ({{ $meeting->organizer->email }}) @endif
- **{{ __('Starts at') }}:** {{ $meeting->scheduled_at->format('Y-m-d H:i') }} ({{ $meeting->timezone ?? 'UTC' }})
- **{{ __('Duration') }}:** {{ $meeting->duration_minutes ?? 60 }} {{ __('minutes') }}
@if($meeting->location)
- **{{ __('Location') }}:** {{ $meeting->location }}
@endif

@component('mail::button', ['url' => $joinUrl])
{{ __('View Details') }}
@endcomponent

{{ __('If you can\'t attend, please update your response in the app.') }}

Thanks,<br>
{{ $appName }}
@endcomponent

