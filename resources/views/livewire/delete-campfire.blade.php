<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">
        Are you sure you want to delete the following Fire?
    </x-slot>

    <h1 class="mb-4 font-semibold text-white">{{ $campfire->name }} Fire</h1>
    <p class="text-white">
        Just so you know if you go ahead with this, there's no turning back. It
        will delete your Fire and break the relationship to your connections.
    </p>
    <x-slot name="buttons">
        <x-secondary-button type="button" wire:click="$emit('modal.close')">
            {{ __('Cancel') }}
        </x-secondary-button>
        <x-primary-button wire:click="handle">
            {{ __('Delete Fire') }}
        </x-primary-button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
