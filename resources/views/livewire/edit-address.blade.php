<x-wire-elements-pro::tailwind.modal>
    <x-slot name="title">Editing Address</x-slot>

    <div class="grid md:grid-cols-2 md:gap-4">
        <x-forms.input
            id="first_name"
            label="First Name"
            name="first_name"
            wire:model="address.first_name"
            :error="$errors->first('address.first_name')"
        />

        <x-forms.input
            id="last_name"
            label="Last Name"
            name="last_name"
            wire:model="address.last_name"
            :error="$errors->first('address.last_name')"
        />

        <x-forms.input
            id="company"
            label="Company"
            name="company"
            wire:model="address.company"
            :error="$errors->first('address.company')"
        />

        <x-forms.input
            id="street_address"
            label="Street Address"
            name="street_address"
            wire:model="address.street_address"
            :error="$errors->first('address.street_address')"
        />

        <x-forms.input
            id="address2"
            label="Address 2"
            name="address2"
            wire:model="address.address2"
            :error="$errors->first('address.address2')"
        />

        <x-forms.input
            id="city"
            label="City"
            name="city"
            wire:model="address.city"
            :error="$errors->first('address.city')"
        />

        <x-forms.input
            id="zip"
            label="Zip"
            name="zip"
            wire:model="address.zip"
            :error="$errors->first('address.zip')"
        />
    </div>

    <x-slot name="buttons">
        <x-primary-button wire:click="handle">
            {{ __('Save Changes') }}
        </x-primary-button>
        <x-secondary-button wire:click="$emit('modal.close')">
            Close
        </x-secondary-button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
