<div class="relative z-50">
    <div class="fixed inset-0 bg-black">
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex items-center min-h-full justify-center text-center">
                <div class="m-0 relative w-full overflow-hidden text-left">
                    <div class="md:flex items-center h-full">
                        <div
                            class="md:w-1/2 md:flex flex-col items-center justify-center bg-[#050504] md:h-screen p-6">

                            <div class="max-w-md mx-auto">

                                <button @click="connectToHost = false">
                                    <x-icons.back/>
                                </button>

                                <x-type.registration-h1 class="my-6">
                                    Connect with {{ $campfire->host->given_name }}
                                </x-type.registration-h1>

                                <form wire:submit.prevent="handle">

                                    <div class="space-y-4">

                                        <div class="md:grid grid-cols-2 gap-4 space-y-4 md:space-y-0">
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

                                            <div class="md:col-span-2">
                                                <x-forms.input
                                                    required
                                                    name="email"
                                                    label="Email"
                                                    wire:model.defer="email"
                                                    :error="$errors->first('email')"
                                                />
                                            </div>
                                            <div class="col-span-2">
                                                <x-forms.input
                                                    name="city"
                                                    required
                                                    label="City"
                                                    wire:model.defer="city"
                                                    :error="$errors->first('city')"
                                                />
                                            </div>
                                            <div class="col-span-2">

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
                                            </div>

                                            <div class="col-span-2">

                                                <x-forms.select
                                                    wire:model="country"
                                                    label="Country"
                                                    id="country"
                                                    name="country"
                                                    required>
                                                    <option value="">Select a Country</option>
                                                    @foreach(getCountries() as $key => $country)
                                                        <option value="{{ $key }}">
                                                            {{ $country }}
                                                        </option>
                                                    @endforeach
                                                </x-forms.select>

                                            </div>

                                        </div>

                                        <div class="md:grid grid-cols-2 gap-4">
                                            <x-forms.country-phone-codes
                                                label="Country Code"
                                                name="countryCode"
                                                wire:model="countryCode"
                                                instruction="Select your country code"
                                            />
                                            <x-forms.input
                                                name="phone"
                                                label="Phone"
                                                wire:model.defer="phone"
                                                type="tel"
                                                instruction="This will only be visible to the host."
                                                :error="$errors->first('phone')"
                                            />

                                        </div>

                                    </div>

                                    <div class="text-grey-200 text-sm mt-6">
                                        <x-forms.label
                                            for="emailUpdates"
                                            label="Updates & Resources"/>
                                        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4
                                        md:space-y-0">
                                            <div class="flex items-stretch rounded-full bg-[#171513]">
                                                <button
                                                    type="button"
                                                    wire:click.prevent="$set('emailUpdates', true)"
                                                    class="w-1/2 px-10 py-3 text-sm font-semibold
                                                    @if($emailUpdates)
                                                    border border-white text-white rounded-l-full
                                                    @endif
                                                    ">
                                                    Yes
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click.prevent="$set('emailUpdates', false)"
                                                    class="w-1/2 px-10 py-3 text-sm font-semibold
                                                    @if(!$emailUpdates) border border-white text-white rounded-r-full
                                                    @endif
                                                    ">
                                                    No
                                                </button>
                                            </div>
                                            <div>
                                                Send me updates and resources from Wild at Heart.
                                            </div>
                                        </div>
                                    </div>


                                    <div class="mt-12">
                                        @include('partials.honeypot')
                                        <x-primary-button
                                            type="submit"
                                            class="w-full">
                                            Connect with {{ $campfire->host->given_name }}
                                        </x-primary-button>

                                    </div>
                                </form>
                            </div>

                        </div>
                        <div class="hidden w-1/2 md:flex flex-col items-center justify-center">
                            <x-campfire-card
                                :title="$campfire->title()"
                                :frequency="$campfire->frequency->name"
                                :location="$campfire->location()"
                                :host-name="$campfire->host->fullName"
                                :profile-image="$campfire->host->image"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
