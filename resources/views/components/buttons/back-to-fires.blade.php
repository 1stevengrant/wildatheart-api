<a
    href="{{ route('home') }}"
    {{ $attributes->class(['absolute left-4 top-[20px] z-50 flex items-center space-x-2 rounded-full bg-white px-3 py-1 text-black']) }}
>
    <x-icons.arrow-left />
    <span class="text-sm">{{ __('Back to Fires') }}</span>
</a>
