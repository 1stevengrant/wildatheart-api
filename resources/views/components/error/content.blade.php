@props([
    'exception',
])

<div
    {{ $attributes->class(['flex h-screen flex-col items-center justify-center']) }}
>
    <div class="text-center text-2xl font-semibold text-white">
        <div class="flex items-center">
            <x-logo class="mx-auto mb-4 w-[300px]" />
            <img
                src="{{ asset('svgs/map-campfire-marker.svg') }}"
                class="mx-auto mb-4 h-[200px]"
                alt=""
            />
        </div>
        <div>
            {{ $exception->getStatusCode() }}
        </div>
        <div class="mx-auto max-w-md">
            {{ $exception->getMessage() }}
        </div>
        <a
            class="border-b border-white text-base hover:border-transparent"
            href="{{ route('home') }}"
        >
            Back to Fires
        </a>
    </div>
</div>
