<x-mail::message>
You need to send a Fire gift to

{{ $campfire->host->full_name }}
@if($campfire->hasDifferentMailingAddress())
@php
$data = $campfire->address->mapbox_place_name;
$sep_tag= explode(',', $data);
@endphp
@foreach($sep_tag as $tag)
- {{ $tag }}
@endforeach
@else
@php
$data = $campfire->mapbox_place_name;
$sep_tag= explode(',', $data);
@endphp
@foreach($sep_tag as $tag)
- {{ $tag }}
@endforeach
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
