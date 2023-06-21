<x-wire-elements-pro::tailwind.modal on-submit="handle">
    <x-slot name="title">
        Edit {{ $campfire->name }} Fire
    </x-slot>

    <div class="space-y-4">
        <x-forms.input
            name="name"
            label="Name"
            wire:model.defer="campfire.name"
            :error="$errors->first('campfire.name')"
        />

    </div>

    <x-slot name="buttons">
        <x-primary-button>
            {{ __('Update Fire') }}
        </x-primary-button>

        <x-secondary-button
            type="button"
            wire:click="$emit('modal.close')">
            {{ __('Cancel') }}
        </x-secondary-button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
