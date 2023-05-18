<x-mail::message>

{{ $campfire->user->given_name }} has requested more books & resources for their 🔥 on {{ prettyDate($campfire->date) }}

<x-mail::button :url="url('/manager/resources/camp-fires/' . $campfire->id)">
Review Fire In Admin
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
