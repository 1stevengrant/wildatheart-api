@props([
    'url',
])

<a
    href="{{ $url }}"
    {{
        $attributes->merge(['class' => 'rounded-lg px-2 py-1 border border-orange hover:border-black
        bg-orange text-white transition-colors duration-100 ease-in-out
        hover:bg-black text-center'])
    }}
>
    {{ $slot }}
</a>
