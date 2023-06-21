<x-mail::message>
{{ $campfire->user->given_name }},

**Keep an eye on your mailbox.** We’ll be sending you a Firestarter Kit to assist you in organizing the event.

We are excited to have you on board and can't wait to see what God does with your Fire.

If you have any questions or require any assistance, please don't hesitate to reach out to us at [{{ config('mail.from.address') }}](mailto:{{ config('mail.from.address') }}).

For the Kingdom,

John and Morgan

<x-mail::button :url="route('view.campfire', $campfire)">
    See & Share Your Fire
</x-mail::button>
</x-mail::message>
