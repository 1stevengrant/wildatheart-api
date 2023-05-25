<nav class="hidden md:flex items-center space-x-4">
    <a class="text-white flex items-center"
       href="{{ route('home') }}">
        <x-icons.fire />
        <span class="ml-2">{{ __('Find a Fire') }}</span>
    </a>
    <a class="text-white flex items-center"
       href="{{ route('host') }}">
        <x-icons.hand-wave />
        <span class="ml-2">{{ __('Host a Fire') }}</span>
    </a>
</nav>
