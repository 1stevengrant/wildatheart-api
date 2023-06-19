<nav class="hidden items-center space-x-4 md:flex">
    <a class="flex items-center text-white" href="{{ route('home') }}">
        <x-icons.fire />
        <span class="ml-2">{{ __('Find a Fire') }}</span>
    </a>
    <a class="flex items-center text-white" href="{{ route('host') }}">
        <x-icons.hand-wave />
        <span class="ml-2">{{ __('Host a Fire') }}</span>
    </a>
</nav>
