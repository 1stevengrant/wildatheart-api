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
            required
            :error="$errors->first('description')"
        />

        <x-forms.input
            required
            name="attendeeLimit"
            label="Attendee Limit"
            type="number"
            min="1"
            wire:model.defer="attendeeLimit"
            :error="$errors->first('attendeeLimit')"
        />

        <x-forms.input
            name="phone"
            required
            label="Phone"
            wire:model.defer="phone"
            type="tel"
            :error="$errors->first('phone')"
        />

        <x-forms.input
            name="address"
            required
            label="Address"
            wire:model.defer="address"
            :error="$errors->first('address')"
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

        <x-forms.input
            name="zip"
            required
            label="Zip"
            wire:model.defer="zip"
            :error="$errors->first('zip')"
        />

        <x-forms.date
            name="date"
            label="Date"
            required
            wire:model.defer="date"
            :error="$errors->first('date')"
            min="{{ now()->format('Y-m-d') }}"
            instruction="Must be in the future and in the format dd/mm/yyyy"
        />

        <x-forms.select
            name="time"
            label="Time"
            required
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
