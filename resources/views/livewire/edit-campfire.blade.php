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
           name="street_address"
           label="Street Address"
           wire:model.defer="campfire.street_address"
           :error="$errors->first('campfire.street_address')"
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

       <div class="space-y-5 hover:cursor-pointer">
           <div class="relative flex items-start">
               <div class="flex h-6 items-center">
                   <input id="is_private"
                          aria-describedby="is-private-event"
                          name="is_private"
                          wire:model="campfire.is_private"
                          type="checkbox"
                          class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-600">
               </div>
               <div class="ml-3 text-sm leading-6">
                   <label for="is_private"
                          class="font-medium text-gray-900 hover:cursor-pointer">Is Private?</label>
                   <p id="is-private-description" class="text-gray-500">
                       This 🔥 will not be publicly listed and can only be accessed by those with the link.
                   </p>
               </div>
           </div>
       </div>

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
