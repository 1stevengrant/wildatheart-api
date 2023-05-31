<x-mail::message>
Hi {{ $campfire->user->first_name }},

Your campfire has been created successfully. Please find the details below.

You can also find attached the PDF starter kit for your fire.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
