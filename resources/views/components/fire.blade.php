@props([
    'fire'
])

<li {{ $attributes->merge(['class' => '']) }}>
    <div>
        {{ $fire->description }}
        <div class="mt-3">
            <span class="font-semibold">When?</span>
            <span class="text-orange-600 text-lg ">{{ prettyDate($fire->date) }} at {{ prettyTime($fire->time) }}</span>
        </div>
    </div>
    <div class="my-6 flex items-center space-x-4">
        <div class="p-4 border border-black">
            {{ $fire->spacesRemaining() }}
        </div>

        <a class="bg-black text-white font-semibold p-4 inline-block"
           target="_blank"
           href="{{route('view.fire', $fire) }}">
            More info
        </a>

        @if(!$fire->isPast() && $fire->isApproved())

            @if(!$fire->isHost())

                @auth
                    @if(!auth()->user()->isAttending($fire))
                        <button
                            wire:click="$emit('modal.open', 'rsvp-for-fire', {'fire' : '{{ $fire->id }}'})"
                            class="bg-black text-white font-semibold p-4 inline-block">
                            RSVP
                        </button>
                    @endif

                @else
                    <button wire:click="handleAttendee('{{ $fire->id }}')"
                            class="bg-blue-600
                                    text-white
                                    font-semibold p-4
                                    inline-block">
                        RSVP
                    </button>
                @endauth
            @endif

        @endif

        @if($fire->isHost())

            @if($fire->isPrivate())
                <div class="inline-block p-4 font-semibold uppercase bg-blue-600 text-white">
                    Private
                </div>
            @endif

            @if(!$fire->isPast())
                <button
                    wire:click="$emit('modal.open', 'edit-fire', {'fire' : '{{ $fire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Edit
                </button>

                <button
                    wire:click="$emit('modal.open', 'delete-fire', {'fire' : '{{ $fire->id }}'})"
                    class="bg-red-600 text-white font-semibold p-4 inline-block">
                    Delete
                </button>
            @endif


            @if(!$fire->isPast() && $fire->isApproved())

                <button
                    wire:click="$emit('modal.open', 'request-more-books', {'fire' : '{{ $fire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Request More Books
                </button>

                <button
                    wire:click="$emit('modal.open', 'attendee-list', {'fire' : '{{ $fire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Attendee List
                </button>

            @endif

            @if(!$fire->isApproved() && !$fire->isPast())
                <div class="inline-block p-4 font-semibold uppercase bg-yellow-200 text-yellow-800">
                    Pending
                </div>
            @endif
        @endif


    </div>
</li>
