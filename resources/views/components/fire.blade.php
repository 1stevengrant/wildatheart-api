@props([
    'fire'
])

<li {{ $attributes->merge(['class' => '']) }}>
    <div>
        {{ $fire->description }}
        <div class="mt-3">
            <span class="font-semibold">When?</span>
            <span class="text-orange-600 text-lg ">{{ prettyDate($fire->date) }}</span>
        </div>
    </div>
    <div class="my-6 flex items-center space-x-4">
        <div class="p-4 border border-black">
            {{ $fire->spacesRemaining() }}
        </div>
        <a class="bg-black text-white font-semibold p-4 inline-block"
           href="{{route('view.fire', $fire) }}">
            More info
        </a>
        @auth
            @if(!auth()->user()->isAttending($fire))
                <button
                        wire:click="$emit('modal.open', 'rsvp-for-fire', {'fire' : {{ $fire->id }}})"
                        class="bg-black text-white font-semibold p-4 inline-block">
                    RSVP
                </button>
            @endif

        @else
            <button wire:click="handleAttendee({{ $fire->id }})"
                    class="bg-blue-600
                                    text-white
                                    font-semibold p-4
                                    inline-block">
                RSVP
            </button>
        @endauth


        @auth
            @if(isset($fire->user) && $fire->user->id === auth()->user()->id)
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
            @endif
        @endauth
    </div>
</li>
