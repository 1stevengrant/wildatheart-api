<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">
        {{ $campfire->date }} at {{ $campfire->time }}
    </x-slot>

    <div>
        <iframe
            width="100%"
            height="300"
            frameborder="0"
            scrolling="no"
            marginheight="0"
            marginwidth="0"
            src="https://maps.google.com/maps?width=100%25&amp;height=300&amp;hl=en&amp;q={{ urlencode
                ($campfire->address) }},{{ urlencode($campfire->city) }},{{ urlencode($campfire->state) }},{{ urlencode($campfire->zip_code) }}&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
    </div>

    <div class="mt-6">
        {{ $campfire->description }}
    </div>

    <x-slot name="buttons">
        <button type="button" wire:click="$emit('modal.close')">
            Close
        </button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
