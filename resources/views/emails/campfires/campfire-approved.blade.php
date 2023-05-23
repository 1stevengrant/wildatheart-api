<x-mail::message>
Hey {{ $campfire->user->given_name }},

Your campfire has been approved!

You can now share with people to connect to your 🔥.

<x-mail::button :url="route('view.campfire', $campfire)">
See & Share Campfire
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
