<x-mail::message>
# Contact invitation

{{ $invitation->invitedBy->fullname }} would like to add you to their contacts for:

@foreach($invitation->events as $event)
* {{ $event->name }} ({{ $event->eventType->name }})
@endforeach

@if($accountExisted)
Sign in to your existing account to review and accept this request.
@else
You will need to create an account to accept this request. Your account and event associations are not created until you confirm.
@endif

<x-mail::button :url="mb_rtrim(config('app.frontend_url'), '/') . '/contact-invitations/respond#token=' . urlencode($token)">
Review request
</x-mail::button>

This invitation expires in seven days. You can also decline it from the review page.

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
