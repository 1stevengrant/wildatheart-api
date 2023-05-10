<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">
        {{ $fire->date }} at {{ $fire->time }}
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
                ($fire->address) }},{{ urlencode($fire->city) }},{{ urlencode($fire->state) }},{{ urlencode($fire->zip_code) }}&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
    </div>

    <div class="mt-6">
        {{ $fire->description }}
    </div>

    <x-slot name="buttons">
        <button type="button" wire:click="$emit('modal.close')">
            Close
        </button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
