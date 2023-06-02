<x-mail::message>
Dear {{ $attendee->user->given_name }},

Thank you for registering for {{ $campfire->title() }}. We can't wait to have you join us for an inspiring and impactful experience. Your commitment to attending this event shows your passion for making a positive change in your life and the lives of other men. 

We're thrilled to have you on board!

In the coming days, we will be sharing more details about the event schedule and logistics. Stay tuned for updates, and feel free to reach out to {{ $campfire->host->given_name }} at {{ $campfire->host->email }} with questions.

For more info you can visit this link to find out more about the Fires program. 

Additionally, you can catch the podcast from Morgan and John to hear more about the heart of the events.

See you at {{ $campfire->title() }}!

Warm regards,
Wild At Heart

</x-mail::message>
