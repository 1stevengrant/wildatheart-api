<div class="relative" id="top">
    <x-type.h1>Attend a Fire in</x-type.h1>

    <nav class="space-x-4 space-y-4">
        @foreach ($states as $state)
            <a
                class="inline-block bg-black p-2 text-white"
                href="#{{ $state }}"
            >
                {{ $state }}
            </a>
        @endforeach
    </nav>

    <nav class="mt-4 space-x-4 space-y-4">
        @foreach ($cities as $city)
            <a
                class="inline-block bg-black p-2 text-white"
                href="{{ route('list.city', ['city' => $city]) }}"
            >
                {{ $city }}
            </a>
        @endforeach
    </nav>

    <div class="mt-6">
        @foreach ($campfires as $state => $stateCampfires)
            <div
                class="{{ $loop->odd ? 'bg-gray-100' : '' }} border-b border-black p-6"
            >
                <x-type.h2 id="{{ $state }}">
                    <a href="{{ route('list.state', strtolower($state)) }}">
                        {{ $state }}
                    </a>
                </x-type.h2>
                <ul class="mt-6">
                    @foreach ($stateCampfires as $campfire)
                        <x-campfire :campfire="$campfire" />
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <a href="#top" class="fixed bottom-10 right-10 bg-black p-4 text-white">
        &uarr;
    </a>
</div>
