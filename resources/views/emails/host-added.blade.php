<x-mail::message>
# You've been added as a host!

Hello {{ $host->fullname }},

**{{ $addedBy->fullname }}** has added you as a host for their event:

## {{ $event->name }} {{ $event->eventTypeSingular() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

As a host, you can now help manage this event, edit details, and view attendees.

<x-mail::button :url="route('my.events.edit', $event)">
View Event
</x-mail::button>

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
