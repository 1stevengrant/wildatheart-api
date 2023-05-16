@props([
    'state'
])
<nav class="font-semibold text-lg">
    <a href="{{ route('attend') }}" class="underline hover:no-underline">
        All Fires
    </a>
    <span>
        &rAarr;
    </span>
    <a href="/attend/state/{{ strtolower($state) }}"
       class="underline hover:no-underline">
        {{ $state }}
    </a>
</nav>
