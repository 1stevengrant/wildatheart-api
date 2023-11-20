<x-mail::message>
Hey {{ $user->given_name }},

Thanks so much for listing your Fire. We're excited to have you join us.

Unfortunately, we noticed a bug in our system that didn't save your mailing address when you first listed your Fire.
    We've fixed that bug, but we need you to now update your mailing address so that we can send your Firestarter Kit.

<x-mail::button :url="route('update.address', $event)">
Update Mailing Address
</x-mail::button>

Thanks and our apologies for the inconvenience,<br>
The Wild at Heart team
</x-mail::message>
