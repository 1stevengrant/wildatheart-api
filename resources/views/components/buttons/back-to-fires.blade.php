<a
    href="{{ route('home') }}"
    {{
        $attributes->class(['absolute left-4 top-[20px] z-10 flex items-center space-x-2 rounded-full bg-black px-3
        py-1 text-white'])
    }}
>
    <x-icons.arrow-left />
    <span class="text-sm">{{ __('Back to Fires') }}</span>
</a>
