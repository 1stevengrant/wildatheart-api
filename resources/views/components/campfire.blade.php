@props([
    'campfire'
])

<li {{ $attributes->merge(['class' => '']) }}>
    <div>
        {{ $campfire->description }}
        <div class="mt-3">
            <span class="font-semibold">When?</span>
            <span class="text-orange-600 text-lg ">{{ prettyDate($campfire->date) }} at {{ prettyTime($campfire->time) }}</span>
        </div>
    </div>
    <div class="my-6 flex items-center space-x-4">
        <div class="p-4 border border-black">
            {{ $campfire->spacesRemaining() }}
        </div>

        <a class="bg-black text-white font-semibold p-4 inline-block"
           target="_blank"
           href="{{ route('view.campfire', $campfire) }}">
            More info
        </a>

        @if(!$campfire->isPast() && $campfire->isApproved())

            @if(!$campfire->isHost())

                @auth
                    @if(!auth()->user()->isAttending($campfire))
                        <button
                            wire:click="$emit('modal.open', 'rsvp-for-campfire', {'campfire' : '{{ $campfire->id }}'})"
                            class="bg-black text-white font-semibold p-4 inline-block">
                            RSVP
                        </button>
                    @endif

                @else
                    <button wire:click="handleAttendee('{{ $campfire->id }}')"
                            class="bg-blue-600
                                    text-white
                                    font-semibold p-4
                                    inline-block">
                        RSVP
                    </button>
                @endauth
            @endif

        @endif

        @if($campfire->isHost())

            @if($campfire->isPrivate())
                <div class="inline-block p-4 font-semibold uppercase bg-blue-600 text-white">
                    Private
                </div>
            @endif

            @if(!$campfire->isPast())
                <button
                    wire:click="$emit('modal.open', 'edit-campfire', {'campfire' : '{{ $campfire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Edit
                </button>

                <button
                    wire:click="$emit('modal.open', 'delete-campfire', {'campfire' : '{{ $campfire->id }}'})"
                    class="bg-red-600 text-white font-semibold p-4 inline-block">
                    Delete
                </button>
            @endif


            @if(!$campfire->isPast() && $campfire->isApproved())

                <button
                    wire:click="$emit('modal.open', 'request-more-books', {'campfire' : '{{ $campfire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Request More Books
                </button>

                <button
                    wire:click="$emit('modal.open', 'attendee-list', {'campfire' : '{{ $campfire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    Attendee List
                </button>

            @endif

            @if(!$campfire->isApproved() && !$campfire->isPast())
                <div class="inline-block p-4 font-semibold uppercase bg-yellow-200 text-yellow-800">
                    Pending
                </div>
            @endif
        @endif


    </div>
</li>
