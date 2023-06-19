<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">Are you sure you want to delete this Fire?</x-slot>

    <x-slot name="buttons">
        <button type="button" wire:click="$emit('modal.close')">Cancel</button>
        <button type="button" wire:click="handle">Delete</button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
