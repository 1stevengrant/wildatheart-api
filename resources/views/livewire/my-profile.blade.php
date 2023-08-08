<x-layouts.account>
    <x-type.registration-h1 class="mb-8">
        {{ __('My Fire Host Profile') }}
    </x-type.registration-h1>
    <div class="max-w-2xl space-y-8">
        <div class="grid gap-8 md:grid-cols-2">
            <x-forms.input
                wire:model="user.given_name"
                label="First Name"
                required
                maxlength="50"
                :error="$errors->first('user.given_name')"
                id="first_name"
                name="first_name"
            />

            <x-forms.input
                name="last_name"
                maxlength="50"
                id="last_name"
                required
                wire:model.defer="user.family_name"
                :error="$errors->first('user.family_name')"
                label="Last Name"
            />
            <div class="col-span-2">
                <div>
                    <x-forms.label for="email" maxlength="255" label="Email" />
                    <div>{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div>
                <x-forms.country-phone-codes
                    label="Country Code"
                    required
                    name="countryCode"
                    wire:model="user.country_code"
                    instruction="Select your country code"
                />
            </div>
            <div>
                <x-forms.input
                    name="phone"
                    id="phone"
                    required
                    wire:model.defer="user.phone"
                    :error="$errors->first('user.phone')"
                    instruction="We may occasionally send you text messages."
                    maxlength="20"
                    minlength="9"
                    label="Phone Number"
                    type="tel"
                />
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <div>
                <input
                    type="file"
                    name="file"
                    id="file"
                    wire:model.defer="photo"
                    class="inputfile outline-none focus:border-none focus:outline-none focus:ring-orange"
                />
                @if (! $photo)
                    <label for="file" class="photo-placeholder">
                        <x-icons.camera />
                    </label>
                @else
                    @if ($isUploaded)
                        <label
                            for="file"
                            style="
                                background-image: url('{{ $photo->temporaryUrl() }}') !important;
                            "
                            class="photo-placeholder"
                        ></label>
                    @else
                        <label
                            for="file"
                            style="
                                background-image: url('{{ getImageUrl($photo) }}') !important;
                            "
                            class="photo-placeholder"
                        ></label>
                    @endif
                @endif
                <div wire:loading wire:target="photo">
                    <x-icons.spinner />
                </div>
            </div>
            <div class="text-sm">
                <x-forms.label for="file" label="Profile Photo" class="mt-2" />
                <x-forms.instruction
                    instruction="Your photo will only be used to help your community identify your
                                fire."
                />

                @error('photo')
                    <x-forms.error :error="$message" />
                @enderror
            </div>
        </div>

        <div class="text-sm text-grey-200">
            <x-forms.label for="emailUpdates" label="Updates & Resources" />
            <div
                class="flex flex-col space-y-4 md:flex-row md:items-center md:space-x-4 md:space-y-0"
            >
                <div class="flex items-center rounded-full bg-form">
                    <button
                        type="button"
                        wire:click.prevent="$set('user.opted_into_marketing', true)"
                        class="@if ($user->opted_into_marketing) border border-white text-white rounded-l-full @endif w-1/2 px-10 py-3 text-sm font-semibold"
                    >
                        Yes
                    </button>
                    <button
                        type="button"
                        wire:click.prevent="$set('user.opted_into_marketing', false)"
                        class="@if (!$user->opted_into_marketing) border border-white text-white rounded-r-full @endif w-1/2 px-10 py-3 text-sm font-semibold"
                    >
                        No
                    </button>
                </div>
                <div>Send me updates and resources from Wild at Heart.</div>
            </div>
        </div>

        <div class="space-x-4">
            <x-primary-button wire:click="handle">
                {{ __('Update Profile') }}
            </x-primary-button>
            <x-secondary-link href="{{ route('home') }}">
                {{ __('Cancel') }}
            </x-secondary-link>
        </div>
    </div>
</x-layouts.account>
