@props([
    'campfire',
])

<li {{ $attributes->merge(['class' => '']) }}>
    <div>
        {{ $campfire->description }}
        <div class="mt-3">
            <div>
                <span class="font-semibold">Where?</span>
                <span class="text-orange-600 text-lg">
                    {{ $campfire->city }}
                </span>
            </div>
            <span class="font-semibold">When?</span>
            <span class="text-orange-600 text-lg">
                {{ prettyDate($campfire->date) }} at
                {{ prettyTime($campfire->time) }}
            </span>
        </div>
    </div>
    <div class="my-6 flex items-center space-x-4">
        <div class="border border-black p-4">
            {{ $campfire->spacesRemaining() }}
        </div>

        <a
            class="inline-block bg-black p-4 font-semibold text-white"
            target="_blank"
            href="{{ route('view.campfire', $campfire) }}"
        >
            More info
        </a>

        <button
            wire:click="$emit('modal.open', 'connect-to-host', {'campfire' : '{{ $campfire->id }}'})"
            class="inline-block bg-black p-4 font-semibold text-white"
        >
            Connect to Host
        </button>

        @if ($campfire->isHost())
            @if ($campfire->isPrivate())
                <div
                    class="bg-blue-600 inline-block p-4 font-semibold uppercase text-white"
                >
                    Private
                </div>
            @endif

            @if (! $campfire->isPast())
                <button
                    wire:click="$emit('modal.open', 'edit-campfire', {'campfire' : '{{ $campfire->id }}'})"
                    class="inline-block bg-black p-4 font-semibold text-white"
                >
                    Edit
                </button>

                <button
                    wire:click="$emit('modal.open', 'delete-campfire', {'campfire' : '{{ $campfire->id }}'})"
                    class="bg-red-600 inline-block p-4 font-semibold text-white"
                >
                    Delete
                </button>
            @endif

            @if (! $campfire->isPast() && $campfire->isApproved())
                <button
                    wire:click="$emit('modal.open', 'request-more-books', {'campfire' : '{{ $campfire->id }}'})"
                    class="inline-block bg-black p-4 font-semibold text-white"
                >
                    Request More Books
                </button>

                <button
                    wire:click="$emit('modal.open', 'attendee-list', {'campfire' : '{{ $campfire->id }}'})"
                    class="inline-block bg-black p-4 font-semibold text-white"
                >
                    Attendee List
                </button>
            @endif

            @if (! $campfire->isApproved() && ! $campfire->isPast())
                <div
                    class="bg-yellow-200 text-yellow-800 inline-block p-4 font-semibold uppercase"
                >
                    Pending
                </div>
            @endif
        @endif
    </div>
</li>
