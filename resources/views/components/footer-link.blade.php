@props([
    'isActive' => false,
])
<a
    {{ $attributes->merge(['class' => 'inline-flex flex-col items-center justify-center px-5 group text-white']) }}
>
    {{ $icon }}
    <span class="text-sm text-white">
        {{ $slot }}
    </span>
    @if ($isActive)
        <span class="h-0.5 w-4 bg-white"></span>
    @endif
</a>
