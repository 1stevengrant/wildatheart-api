<x-mail::message>
# You've been invited to take ownership of an event!

Hello,

**{{ $transferredByName }}** wants to transfer ownership of their event to you:

## {{ $event->name }} {{ $event->eventTypeSingular() }}

**Location:** {{ $event->location }}
@if($event->formatted_start_date)
**Date:** {{ $event->formatted_start_date }}
@endif

As the new owner, you'll have full control over this event, including the ability to edit all details, manage hosts, and transfer ownership in the future. The current owner will lose all access to this event.

<x-mail::button :url="$acceptUrl">
Accept Ownership Transfer
</x-mail::button>

Click the button above to accept this ownership transfer. If you don't have an account yet, you'll be able to create one and will automatically become the owner.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
