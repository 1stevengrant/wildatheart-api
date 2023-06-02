<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">
        Fire Attendee List for {{ prettyDate($campfire->date) }}
    </x-slot>
    <div class="mb-4">
        Total Attendees: {{ $campfire->attendees->count() }}
    </div>
    <ul role="list" class="shadow-inner divide-y divide-gray-100 overflow-y-scroll max-h-[400px]">
        @foreach($campfire->attendees as $attendee)
            <li class="flex justify-between items-center gap-x-6 p-5">
                <div class="flex gap-x-4">
                    <img class="h-12 w-12 flex-none rounded-full bg-gray-50"
                         src="{{ Gravatar::get($attendee->user->email) }}"
                         alt="">
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm font-semibold leading-6 text-gray-900">{{ $attendee->user->full_name }}</p>
                        <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                            {{ $attendee->user->email }}
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex sm:flex-col sm:items-end">
                    <a href="tel:{{ $attendee->phone }}"
                       class="text-sm leading-6 text-gray-900">
                        {{ $attendee->phone }}
                    </a>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Connected
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
            class="bg-black text-white font-semibold p-4 inline-block">
            Close
        </button>
    </x-slot>

</x-wire-elements-pro::tailwind.modal>
