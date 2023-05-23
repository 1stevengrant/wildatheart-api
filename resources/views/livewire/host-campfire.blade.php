<div>

    <x-type.h1>
        Host a 🔥
    </x-type.h1>

    <form wire:submit.prevent="handle"
          class="space-y-4 mt-6"
    >

        <x-forms.input
            name="organization"
            label="Organization"
            wire:model.defer="organization"
            :error="$errors->first('organization')"
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

        <x-forms.select
            name="frequency"
            label="Frequency"
            required
            :error="$errors->first('frequency')"
            wire:model.defer="frequency">
            <option value="">How often is this happening?</option>
            @foreach(\App\Enums\CampfireFrequency::values() as $frequency)
                @ray($frequency)
                <option value="{{ $frequency }}">
                    {{ $frequency }}
                </option>
            @endforeach
        </x-forms.select>

        <div class="space-y-5 hover:cursor-pointer">
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input id="is_private"
                           aria-describedby="is-private-event"
                           name="is_private"
                           wire:model="isPrivate"
                           type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
                </div>
                <div class="ml-3 text-sm leading-6">
                    <label for="is_private"
                           class="font-medium text-gray-900">Is this a private gathering?</label>
                    <p id="comments-description" class="text-gray-500">
                        This 🔥 will not be publicly listed and can only be accessed by those you share the link with
                    </p>
                </div>
            </div>
        </div>

        <button class="p-4 bg-black text-white font-semibold">
            Create Your 🔥
        </button>
    </form>
</div>
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Host a 🔥 | {{ config('app.name') }}';
    });
    </script>
@endpush
