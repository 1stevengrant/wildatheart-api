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

    <div class="mt-6">
        @foreach($fires as $state => $stateFires)
            <div class="border-b border-black p-6 {{ $loop->odd ? 'bg-gray-100' : '' }}">
                <x-type.h2
                    id="{{ $state }}">
                    {{ $state }}
                </x-type.h2>
                <ul class="mt-6">
                    @foreach($stateFires as $fire)
                        <li>
                            <div>
                                {{ $fire->description }}
                                <div class="mt-3">
                                    <span class="font-semibold">When?</span>
                                    <span class="text-orange-600 text-lg ">{{ $fire->date }}</span>
                                </div>
                            </div>
                            <div class="my-6 flex items-center space-x-4">
                                <div class="p-4 border border-black">
                                    {{ $fire->remaining }} / {{ $fire->attendee_limit }} of spaces remaining
                                </div>
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
                                <button
                                    wire:click="$emit('modal.open', 'edit-fire', {'fire' : {{ $fire->id }}})"
                                    class="bg-black text-white font-semibold p-4 inline-block">
                                    Edit
                                </button>
                                <button
                                    wire:click="$emit('modal.open', 'delete-fire', {'fire' : {{ $fire->id }}})"
                                    class="bg-red-600 text-white font-semibold p-4 inline-block">
                                    Delete
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
