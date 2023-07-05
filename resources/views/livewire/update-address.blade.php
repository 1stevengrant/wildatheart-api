<div class="p-4">
    @if (! $addressUpdated)
        <h2 class="text-2xl font-bold">Update Address for {{ $campfire->name }} Fire</h2>
        <p class="mt-4">{{ $campfire->user->given_name }}, please update your mailing address so that we can ship your
            starter
            kit.</p>
        <p class="mt-4"><span class="text-red-500">*</span> denotes required field</p>
        <form wire:submit.prevent="handle" class="mt-6 space-y-4">
            <div class="">
                <label for="address1" class="block font-semibold">
                    Street Address 1 <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    wire:model="street_address"
                    class="border border-grey-darker p-4 text-lg"
                    id="address2"
                    placeholder="Street Address 1"
                />
                @error('street_address')
                    <div class="mt-1 text-red-500">{{ $message }}</div>
                @enderror
            </div>
            <div class="">
                <label for="address2" class="block font-semibold">
                    Street Address 2
                </label>
                <input
                    type="text"
                    wire:model="address2"
                    class="border border-grey-darker p-4 text-lg"
                    id="address2"
                    placeholder="Street Address 2"
                />
                @error('address2')
                    <div class="mt-1 text-red-500">{{ $message }}</div>
                @enderror
            </div>
            <div class="">
                <label for="company" class="block font-semibold">
                    Company
                </label>
                <input
                    type="text"
                    wire:model="company"
                    class="border border-grey-darker p-4 text-lg"
                    id="company"
                    placeholder="Company"
                />
                @error('address2')
                <div class="mt-1 text-red-500">{{ $message }}</div>
                @enderror
            </div>
            <div>
                {{ $campfire->city }}@isset($campfire->state), {{ $campfire->state }}@endisset {{ $campfire->zip }}
            </div>
            <div>
                {{ $campfire->country }}
            </div>
            <div>
                <button
                    type="submit"
                    class="bg-black px-4 py-2 font-bold text-white"
                >
                    Update Address Information
                </button>
            </div>
        </form>
    @else
        <h2 class="text-green-500 text-2xl font-bold">
            Thanks {{ $campfire->user->given_name }}. Your address has been updated!
        </h2>
    @endif
</div>
