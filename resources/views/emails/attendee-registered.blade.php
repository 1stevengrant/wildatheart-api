<x-mail::message>
{{ $campfire->host->given_name }},

We’ve got exciting news — {{ $attendee->fullName }} has expressed interest in your Fire!

Here’s the scoop:

- {{ $attendee->fullName }}
- {{ $attendee->email }}
- {{ $attendee->phone }}

{{ $attendee->fullName }} is expecting to hear from you soon, so be sure to reach out.

It's working. Your choice to risk hosting a Fire is already giving others hope.

Please don't hesitate to reach out if you have any questions or need assistance. We're honored to be alongside you in this mission.

For the Kingdom,
Wild at Heart
</x-mail::message>
