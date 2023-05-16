<x-mail::message>

{{ $fire->user->given_name }} has requested more books & resources for their 🔥 on {{ prettyDate($fire->date) }}

<x-mail::button :url="url('/manager/resources/camp-fires/' . $fire->id)">
Review Fire In Admin
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
