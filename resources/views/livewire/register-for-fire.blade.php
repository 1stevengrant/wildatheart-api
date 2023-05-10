<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        Register for 🔥 on {{ $fire->date }} at {{ $fire->time }}
    </x-slot>

    <div class="space-y-4">
        <x-forms.input
            name="firstName"
            label="First Name"
            wire:model.defer="firstName"
            :error="$errors->first('firstName')"
        />

        <x-forms.input
            name="lastName"
            label="Last Name"
            wire:model.defer="lastName"
            :error="$errors->first('lastName')"
        />

        <x-forms.input
            name="phone"
            label="Phone"
            wire:model.defer="phone"
            type="tel"
            :error="$errors->first('phone')"
        />

        <x-forms.input
            name="email"
            label="Email"
            wire:model.defer="email"
            :error="$errors->first('email')"
        />

        <x-forms.input
            name="confirmEmail"
            label="Confirm Email"
            wire:model.defer="confirmEmail"
            :error="$errors->first('confirmEmail')"
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
