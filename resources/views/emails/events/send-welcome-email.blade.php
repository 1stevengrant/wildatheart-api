<x-mail::message>
{!! Str::markdown($emailContent) !!}
<x-mail::button :url="route('view.event', $event)">
See & Share Your Fire
</x-mail::button>

</x-mail::message>
