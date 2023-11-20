<x-mail::message>
{{ $event->user->given_name }},

It’s official—your Fire is live. Welcome to the revolution!

Here are a few suggestions:

**Share your fire.** Here’s your unique link to share with anyone who may be interested: [Fire Link]({{ $event->url }})

**Respond in a timely manner.** You'll receive an email every time someone expresses interest in your event. It’s up
    to you to communicate all event details (date/time, location, etc). Please respond promptly as they’ll be waiting to hear from you.

**Keep an eye on your mailbox.** We’ll be sending you a Firestarter Kit to assist you in organizing the event.

Men were never meant to bear the weight of their pain or their glory alone. So we lit a Fire. And now you are too!

Fires are a place men find themselves outrageously loved by God, and other men.

We are excited to have you on board and can't wait to see what God does with your Fire.

If you have any questions or require any assistance, please don't hesitate to reach out to us at [{{ config('mail.from.address') }}](mailto:{{ config('mail.from.address') }}).

For the Kingdom,

John and Morgan

<x-mail::button :url="route('view.event', $event)">
See & Share Your Fire
</x-mail::button>

</x-mail::message>
