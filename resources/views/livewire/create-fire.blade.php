<div>

    <x-type.h1>
        Host a 🔥
    </x-type.h1>

    <form wire:submit.prevent="handle"
          class="space-y-4 mt-6"
    >
        <x-forms.input
            name="description"
            label="Description"
            wire:model.defer="description"
            :error="$errors->first('description')"
        />

        <x-forms.input
            name="attendeeLimit"
            label="Attendee Limit"
            type="number"
            min="1"
            wire:model.defer="attendeeLimit"
            :error="$errors->first('attendeeLimit')"
        />

        <x-forms.input
            name="phone"
            label="Phone"
            wire:model.defer="phone"
            type="tel"
            :error="$errors->first('phone')"
        />

        <x-forms.input
            name="address"
            label="Address"
            wire:model.defer="address"
            :error="$errors->first('address')"
        />

        <x-forms.input
            name="city"
            label="City"
            wire:model.defer="city"
            :error="$errors->first('city')"
        />

        <x-forms.select
            name="state"
            label="State"
            :error="$errors->first('state')"
            wire:model.defer="state">
            <option value="">Select a State</option>
            @foreach($states as $state)
                <option value="{{ $state->name }}">
                    {{ $state->code }}
                </option>
            @endforeach
        </x-forms.select>

        <x-forms.input
            name="zip"
            label="Zip"
            wire:model.defer="zip"
            :error="$errors->first('zip')"
        />

        <x-forms.date
            name="date"
            label="Date"
            wire:model.defer="date"
            :error="$errors->first('date')"
            min="{{ now()->format('Y-m-d') }}"
            instruction="Must be in the future and in the format dd/mm/yyyy"
        />

        <x-forms.select
            name="time"
            label="Time"
            :error="$errors->first('time')"
            wire:model.defer="time">
            <option value="">Select a Time</option>
            @foreach($times as $time)
                <option value="{{ $time }}">
                    {{ $time }}
                </option>
            @endforeach
        </x-forms.select>

        <button class="p-4 bg-black text-white font-semibold">
            Create 🔥
        </button>
    </form>
</div>
