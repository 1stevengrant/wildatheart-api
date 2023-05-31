<x-layouts.base>
    <div class="h-screen flex flex-col items-center justify-center">
        <div class="text-white text-center text-2xl font-semibold">
            <img src="{{ asset('svgs/map-campfire-marker.svg') }}"
                 class="h-[200px] mx-auto mb-4"
                 alt="">
            <div>
                {{ $exception->getStatusCode() }}
            </div>
            <div>
                {{$exception->getMessage()}}
            </div>
            <a class="text-base border-b border-white hover:border-transparent" href="{{ route('home') }}">Back to Fires</a>
        </div>
    </div>
</x-layouts.base>
