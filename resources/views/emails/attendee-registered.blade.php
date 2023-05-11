<x-mail::message>
# Someone has registered for your 🔥

Hey there,
{{ $attendee->fullName }} has registered for your 🔥

- Email: {{ $attendee->email }}
- Phone: {{ $attendee->phone }}

You have {{ $fire->remaining }} / {{ $fire->attendee_limit }} spaces remaining.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
