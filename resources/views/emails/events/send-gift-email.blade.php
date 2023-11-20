<x-mail::message>
You need to send a Fire gift to

{{ $event->host->full_name }}
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
