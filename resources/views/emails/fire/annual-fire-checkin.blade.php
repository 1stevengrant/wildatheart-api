<x-mail::message>
# Happy New Year, {{ $hostNames }}!

As we begin a new year, we're reaching out to all our Fire hosts to make sure the fires listed on Wild at Heart are still active and welcoming new members.

**Your fire:** {{ $event->name }}

We want to ensure that people searching for community can find fires that are actively meeting. If your fire is still burning, please let us know by clicking the button below.

@include('emails.components.button', [
    'url' => $burnLink,
    'text' => 'Keep My Fire Burning',
    'color' => 'green',
])

If your fire is no longer meeting or you'd like to take it off the map, you can extinguish it here:

@include('emails.components.button', [
    'url' => $extinguishLink,
    'text' => 'Extinguish My Fire',
    'color' => 'red',
])

**Please respond within 7 days.** If we don't hear from you, we'll send a reminder. After 10 days without confirmation, your fire will be automatically removed from the map to ensure we're showing accurate information to those seeking community.

Thank you for leading and hosting your fire. The work you're doing matters.

With gratitude,<br>
The Wild at Heart Team
</x-mail::message>
