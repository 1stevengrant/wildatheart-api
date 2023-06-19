<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">
        Fire Attendee List for {{ prettyDate($campfire->date) }}
    </x-slot>
    <div class="mb-4">
        Total Attendees: {{ $campfire->attendees->count() }}
    </div>
    <ul
        role="list"
        class="divide-gray-100 max-h-[400px] divide-y overflow-y-scroll shadow-inner"
    >
        @foreach ($campfire->attendees as $attendee)
            <li class="flex items-center justify-between gap-x-6 p-5">
                <div class="flex gap-x-4">
                    <img
                        class="bg-gray-50 h-12 w-12 flex-none rounded-full"
                        src="{{ Gravatar::get($attendee->user->email) }}"
                        alt=""
                    />
                    <div class="min-w-0 flex-auto">
                        <p
                            class="leading-6 text-gray-900 text-sm font-semibold"
                        >
                            {{ $attendee->user->full_name }}
                        </p>
                        <p
                            class="leading-5 text-gray-500 mt-1 truncate text-xs"
                        >
                            {{ $attendee->user->email }}
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex sm:flex-col sm:items-end">
                    <a
                        href="tel:{{ $attendee->phone }}"
                        class="leading-6 text-gray-900 text-sm"
                    >
                        {{ $attendee->phone }}
                    </a>
                    <p class="leading-5 text-gray-500 mt-1 text-xs">
                        Connected
                        <time datetime="2023-01-23T13:23Z">
                            {{ $attendee->created_at->diffForHumans() }}
                        </time>
                    </p>
                </div>
            </li>
        @endforeach
    </ul>

    <x-slot name="buttons">
        <button
            wire:click="$emit('modal.close')"
            class="inline-block bg-black p-4 font-semibold text-white"
        >
            Close
        </button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
