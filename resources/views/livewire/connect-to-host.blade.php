<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        Connect to 🔥 on {{ $campfire->date }} at {{ $campfire->time }}
    </x-slot>

    <div class="space-y-4">

        @if(!auth()->check())
            <x-forms.input
                required
                name="first_name"
                label="First Name"
                wire:model.defer="firstName"
                :error="$errors->first('firstName')"
            />

            <x-forms.input
                required
                name="last_name"
                label="Last Name"
                wire:model.defer="lastName"
                :error="$errors->first('lastName')"
            />

            <x-forms.input
                required
                name="email"
                label="Email"
                wire:model.defer="email"
                :error="$errors->first('email')"
            />

            <x-forms.input
                required
                name="confirm_email"
                label="Confirm Email"
                wire:model.defer="confirmEmail"
                :error="$errors->first('confirmEmail')"
            />

            <x-forms.input
                name="city"
                required
                label="City"
                wire:model.defer="city"
                :error="$errors->first('city')"
            />

            <x-forms.select
                name="state"
                label="State"
                required
                :error="$errors->first('state')"
                wire:model.defer="state">
                <option value="">Select a State</option>
                @foreach($states as $state)
                    <option value="{{ $state->name }}">
                        {{ $state->code }}
                    </option>
                @endforeach
            </x-forms.select>

        @endif

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
