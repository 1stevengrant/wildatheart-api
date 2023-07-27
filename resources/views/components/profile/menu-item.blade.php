@props([
    'slug',
])
<a
    {{ $attributes }}
    class="@if(Request::is($slug)) bg-white/10 @endif flex w-full space-x-4 rounded-lg px-4 py-5"
>
    {{ $icon }}
    <span>
        {{ $slot }}
    </span>
</a>
