<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        Edit 🔥 {{ prettyDate($campfire->date) }} at {{ prettyTime($campfire->time) }}
    </x-slot>

   <div class="space-y-4">
       <x-forms.input
           name="description"
           label="Description"
           wire:model.defer="campfire.description"
           :error="$errors->first('campfire.description')"
       />

       <x-forms.input
           name="attendeeLimit"
           label="Attendee Limit"
           type="number"
           min="1"
           wire:model.defer="campfire.attendee_limit"
           :error="$errors->first('campfire.attendee_limit')"
       />

       <x-forms.input
           name="phone"
           label="Phone"
           wire:model.defer="campfire.phone"
           type="tel"
           :error="$errors->first('campfire.phone')"
       />

       <x-forms.input
           name="address"
           label="Address"
           wire:model.defer="campfire.address"
           :error="$errors->first('campfire.address')"
       />

       <x-forms.input
           name="city"
           label="City"
           wire:model.defer="campfire.city"
           :error="$errors->first('campfire.city')"
       />

       <x-forms.select
           name="state"
           label="State"
           :error="$errors->first('campfire.state')"
           wire:model.defer="campfire.state">
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
           wire:model.defer="campfire.zip"
           :error="$errors->first('campfire.zip')"
       />

       <x-forms.date
           name="date"
           label="Date"
           value=""
           wire:model.defer="campfire.date"
           :error="$errors->first('campfire.date')"
           min="{{ now()->format('Y-m-d') }}"
           instruction="Must be in the future and in the format dd/mm/yyyy"
       />

       <x-forms.select
           name="time"
           label="Time"
           :error="$errors->first('campfire.time')"
           wire:model.defer="campfire.time">
           <option value="">Select a Time</option>
           @foreach($times as $time)
               <option value="{{ $time }}">
                   {{ $time }}
               </option>
           @endforeach
       </x-forms.select>
   </div>

    <x-slot name="buttons">
        <button type="submit"
                class="p-4 bg-black text-white font-semibold">
            Update 🔥
        </button>
        <button
            wire:click="$emit('modal.close')"
            class="bg-black text-white font-semibold p-4 inline-block">
            Cancel
        </button>
    </x-slot>

</x-wire-elements-pro::tailwind.modal>
