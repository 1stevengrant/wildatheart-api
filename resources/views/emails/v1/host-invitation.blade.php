<x-mail::message>
# You've been invited to host an event!

Hello,

**{{ $invitedByName }}** has invited you to be a host for their event:

## {{ $event->name }} {{ $event->eventTypeSingular() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

As a host, you'll be able to help manage this event, edit details, and view attendees.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

Click the button above to accept this invitation. If you don't have an account yet, you'll be able to create one and will automatically become a host.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
