<x-mail::message>
Dear {{ $campfire->host->given_name }},

We are thrilled to inform you that an attendee has registered for your Fire:

- {{ $attendee->fullName }}
- {{ $attendee->email }}
- {{ $attendee->phone }}

Your dedication to making a difference is already attracting enthusiastic participants who are eager to join you on this journey.

Thank you for being a part of Wild at Heart Fires. For more info you can visit this link to find out more about the Fires program.

Additionally, you can catch the podcast from Morgan and John to hear more about the heart of the events.

Please don't hesitate to reach out if you have any questions or need assistance. We're here to support you every step of the way.

Best regards,
Wild At Heart

</x-mail::message>
