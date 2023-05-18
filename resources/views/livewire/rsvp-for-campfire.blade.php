<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        RSVP for 🔥 on {{ $campfire->date }} at {{ $campfire->time }}
    </x-slot>

    <div class="space-y-4">

        <x-forms.input
            required
            name="phone"
            label="Phone"
            wire:model.defer="phone"
            type="tel"
            :error="$errors->first('phone')"
        />

        <x-forms.input
            required
            name="confirm_phone"
            label="Confirm Phone"
            wire:model.defer="confirmPhone"
            type="tel"
            :error="$errors->first('confirmPhone')"
        />
    </div>

    <x-slot name="buttons">
        <button type="submit"
                class="bg-black border border-black text-white font-semibold p-4 inline-block">
            Register
        </button>
        <button
            class="border border-black font-semibold p-4 inline-block"
            type="button"
            wire:click="$emit('modal.close')">
            Close
        </button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
