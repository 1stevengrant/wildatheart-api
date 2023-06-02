<x-mail::message>
Dear {{ $campfire->user->given_name }},

We're excited to inform you that your Fire has been approved. 

We appreciate your patience and commitment to these events. 

If you need any assistance in setting up the Fire or have any questions, please don't hesitate to reach out.

<x-mail::button :url="route('view.campfire', $campfire)">
See & Share Your Fire
</x-mail::button>

Warm regards,
Wild At heart
</x-mail::message>
