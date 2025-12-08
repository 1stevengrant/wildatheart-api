<x-mail::message>
@if($isForNewOwner)
# You are now the owner!

Hello {{ $newOwner->fullname }},

You have successfully accepted ownership of:

## {{ $event->name }} {{ $event->eventTypeSingular() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

You now have full control over this event. The previous owner ({{ $previousOwner->fullname }}) has been added as a host and will retain edit access.

<x-mail::button :url="route('my.events.edit', $event)">
Manage Event
</x-mail::button>
@else
# Ownership has been transferred

Hello {{ $previousOwner->fullname }},

Ownership of your event has been successfully transferred to **{{ $newOwner->fullname }}**:

## {{ $event->name }} {{ $event->eventTypeSingular() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

You are now a **host** of this event and will retain edit access. You can continue to manage event details, view attendees, and invite other hosts.

<x-mail::button :url="route('my.events.edit', $event)">
Manage Event
</x-mail::button>
@endif

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
