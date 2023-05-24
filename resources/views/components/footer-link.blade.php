@props([
    'isActive' => false
])
<a {{ $attributes->merge(['class' => 'inline-flex flex-col items-center justify-center px-5 group'])
}} >
    {{ $icon }}
    <span class="text-sm text-white">
                {{ $slot }}
            </span>
    @if($isActive)
    <span class="w-4 h-0.5 bg-white">
    </span>
    @endif
</a>
