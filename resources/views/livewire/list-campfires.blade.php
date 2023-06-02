<div class="relative" id="top">
    <x-type.h1>
        Attend a Fire in
    </x-type.h1>

    <nav class="space-x-4 space-y-4">
        @foreach($states as $state)
            <a class="p-2 bg-black text-white inline-block" href="#{{ $state }}">
                {{ $state }}
            </a>
        @endforeach
    </nav>

    <nav class="space-x-4 space-y-4 mt-4">
        @foreach($cities as $city)
            <a class="p-2 bg-black text-white inline-block" href="{{ route('list.city', ['city' => $city]) }}">
                {{ $city }}
            </a>
        @endforeach
    </nav>

    <div class="mt-6">
        @foreach($campfires as $state => $stateCampfires)
            <div class="border-b border-black p-6 {{ $loop->odd ? 'bg-gray-100' : '' }}">
                <x-type.h2
                    id="{{ $state }}">
                    <a href="{{ route('list.state', strtolower($state)) }}">
                        {{ $state }}
                    </a>
                </x-type.h2>
                <ul class="mt-6">
                    @foreach($stateCampfires as $campfire)
                        <x-campfire :campfire="$campfire"/>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <a href="#top" class="fixed right-10 bottom-10 bg-black text-white p-4">
        &uarr;
    </a>

</div>

@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Attend a Fire | {{ config('app.name') }}';
    });
    </script>
@endpush
