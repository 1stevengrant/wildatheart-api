<x-layouts.base>
    <div class="h-screen flex flex-col items-center justify-center">
        <div class="text-white text-center text-2xl font-semibold">
            <div class="flex items-center">
                <x-logo class="w-[300px] mx-auto mb-4"/>
                <img src="{{ asset('svgs/map-campfire-marker.svg') }}"
                     class="h-[200px] mx-auto mb-4"
                     alt="">
            </div>
            <div>
                500
            </div>
            <div class="max-w-md">
                Whoops! It's not you, it's us. We're a moment and our engineers are working on it.
            </div>
            <a class="text-base border-b border-white hover:border-transparent"
               href="{{ route('home') }}">
                Back to Fires
            </a>
        </div>
    </div>

</x-layouts.base>
