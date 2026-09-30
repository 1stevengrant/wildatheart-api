<x-mail::message>
# Event interest cancelled

Hello {{ $hostNames }},

{{ $user->fullname }} has cancelled their interest in {{ $event->emailName() }}.

Their contact has been removed from your event contacts dashboard. No action is required from you.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
