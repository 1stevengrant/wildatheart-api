<div class="relative" id="top">
    <x-type.h1>
        Attend a 🔥 in
    </x-type.h1>

    <nav class="space-x-4 space-y-4">
        @foreach($states as $state)
            <a class="p-2 bg-black text-white inline-block" href="#{{ $state }}">
                {{ $state }}
            </a>
        @endforeach
    </nav>

    <div class="mt-6 space-y-4">
        @foreach($fires as $state => $stateFires)
            <div class="border-b border-black pb-6">
                <x-type.h2 id="{{ $state }}">
                    {{ $state }}
                </x-type.h2>
                <ul>
                    @foreach($stateFires as $fire)
                        <li>
                            <div>
                                {{ $fire->description }} - {{ $fire->date }}
                            </div>
                            <div class="my-6">
                                <button
                                    wire:click="$emit('modal.open', 'fire-more-info-modal', {'fire' : {{ $fire->id }}})"
                                    class="bg-black text-white font-semibold p-4 inline-block">
                                    More info
                                </button>
                                <button
                                    wire:click="$emit('modal.open', 'register-for-fire', {'fire' : {{ $fire->id }}})"
                                    class="bg-black text-white font-semibold p-4 inline-block">
                                    Register
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <a href="#top" class="fixed right-10 bottom-10 bg-black text-white p-4">
        &uarr;
    </a>

</div>
