@props([
    'state'
])
<nav class="font-semibold text-lg">
    <a href="{{ route('list.campfires') }}" class="underline hover:no-underline">
        All Fires
    </a>
    <span>
        &rAarr;
    </span>
    <a href="{{ route('list.state', ['state' => strtolower($state)]) }}"
       class="underline hover:no-underline">
        {{ $state }}
    </a>
</nav>
