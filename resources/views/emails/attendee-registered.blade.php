<x-mail::message>
# Someone has registered for your 🔥

Hey there,
{{ $attendee->user->fullName }} has registered for your 🔥

- Email: {{ $attendee->user->email }}
- Phone: {{ $attendee->user->phone }}
- City: {{ $attendee->user->city }}
- State: {{ $attendee->user->state }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
