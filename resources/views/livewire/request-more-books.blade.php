<x-wire-elements-pro::tailwind.modal on-submit="handle">
    <x-slot name="title">Request More Books</x-slot>

    <div>
        <p>
            Are you sure you wish to request more books for this Fire on
            {{ prettyDate($campfire->date) }}?
        </p>
        @if ($campfire->book_request_count > 0)
            <p>
                You have requested {{ $campfire->book_request_count }} times
                already
            </p>
        @endif
    </div>

    <x-slot name="buttons">
        <button type="submit" class="bg-green-600 p-4 font-semibold text-white">
            Yes
        </button>
        <button
            wire:click="$emit('modal.close')"
            class="inline-block border border-black bg-white p-4 font-semibold text-black"
        >
            Cancel
        </button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
