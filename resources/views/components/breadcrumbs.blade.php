@props([
    'state' => false,
    'city' => false,
])
<nav class="text-lg font-semibold">
    <a
        href="{{ route('list.campfires') }}"
        class="underline hover:no-underline"
    >
        All Fires
    </a>
    @if ($state)
        <span>&rAarr;</span>

        <a
            href="{{ route('list.state', ['state' => strtolower($state)]) }}"
            class="underline hover:no-underline"
        >
            {{ $state }}
        </a>
    @endif

    @if ($city)
        <span>&rAarr;</span>
        <a
            href="{{ route('list.city', ['city' => strtolower($city)]) }}"
            class="underline hover:no-underline"
        >
            {{ $city }}
        </a>
    @endif
</nav>
