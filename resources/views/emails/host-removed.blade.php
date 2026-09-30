<x-mail::message>
# You've been removed as a host

Hello {{ $host->fullname }},

**{{ $removedBy->fullname }}** has removed you as a host from their event:

## {{ $event->emailName() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

You no longer have access to manage this event.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
