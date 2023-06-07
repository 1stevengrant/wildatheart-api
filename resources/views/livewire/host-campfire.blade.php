<div class="md:flex h-screen text-white"
     x-data="{ step: @entangle('step') }">

    <div x-show="step === 1"
         class="md:w-1/2 mt-8 px-8 flex items-center justify-center relative">

        <div
            class="md:w-2/3 mx-auto pb-8 md:pb-0">

            <x-profile-stepper :step="$step"/>

            <x-type.registration-h1 class="mt-12">
                We're stoked you want to start a Fire! Just a few questions.
            </x-type.registration-h1>

            <p class="mt-8">One of the most important aspects of our Fires are the hosts that commit to them. </p>

            <div class="space-y-8 mt-6">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <x-forms.country-phone-codes
                            label="Country Code"
                            required
                            name="countryCode"
                            wire:model="countryCode"
                            instruction="Select your country code"
                        />
                    </div>
                    <div>
                        <x-forms.input
                            name="phone"
                            id="phone"
                            required
                            wire:model.defer="phone"
                            :error="$errors->first('phone')"
                            instruction="We'll send you text messages."
                            label="Phone Number"/>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div>
                        <input type="file"
                               name="file"
                               id="file"
                               wire:model.defer="photo"
                               class="inputfile outline-none focus:border-none focus:outline-none focus:ring-orange"/>
                        @if(!$photo)
                            <label for="file"
                                   class="w-[134px] h-[134px] rounded-full bg-[#D9D9D9]/20 flex justify-center
                        items-center mx-auto focus:ring-orange focus:ring">
                                <x-icons.camera/>
                            </label>
                        @else
                            <label for="file"
                                   style="background-image: url('{{ $photo->temporaryUrl() }}') !important;"
                                   class="w-[134px] h-[134px] rounded-full bg-[#D9D9D9] flex justify-center
                                   bg-cover"></label>
                        @endif

                    </div>
                    <div class="text-sm">
                        <x-forms.label
                            for="file"
                            label="Profile Photo"
                            class="mt-2"/>
                        <x-forms.instruction instruction="Your photo will only be used to help your community identify your
                                fire.
                                Wild at Heart will never
                                use your
                                photo for anything other than your Fire page."/>

                        @error('photo')
                        <x-forms.error :error="$message"/>
                        @enderror
                    </div>
                </div>

                <div class="text-grey-200 text-sm">
                    <x-forms.label
                        for="emailUpdates"
                        label="Updates & Resources"/>
                    <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-4 md:space-y-0">
                        <div class="flex items-center rounded-full bg-[#171513]">
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', true)"
                                class="px-10 py-3 text-sm font-semibold w-1/2
                                @if($emailUpdates)
                                border border-white text-white rounded-l-full
                                @endif"
                            >
                                Yes
                            </button>
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', false)"
                                class="px-10 py-3 text-sm font-semibold w-1/2
                                @if(!$emailUpdates) border border-white text-white rounded-r-full
                                @endif"
                            >
                                No
                            </button>
                        </div>
                        <div>
                            Send me updates and resources from Wild at Heart.
                        </div>
                    </div>
                </div>

                <x-button-container>
                    @include('partials.honeypot')
                    <x-primary-button
                        class="w-full"
                        type="submit"
                        wire:click.prevent="handleStepOne">
                        Next
                    </x-primary-button>

                    <a href="{{ route('home') }}" class="text-sm py-3 text-grey-200">
                        Cancel
                    </a>

                </x-button-container>
            </div>
        </div>
    </div>

    <div x-show="step === 2"
         class="md:w-1/2 mt-8 px-8 flex items-center justify-center">

        <div
            class="md:w-2/3 mx-auto relative">

            <x-profile-stepper :step="$step"/>

            <x-type.registration-h1 class="my-12">
                Tell us about your Fire
            </x-type.registration-h1>

            <div class="space-y-4">

                <div class="flex items-center space-x-4">
                    <x-forms.input
                        wire:model="campfireName"
                        label="Name Your Fire"
                        required
                        instruction="This is the name that will be displayed on your Fire page."
                        :error="$errors->first('campfireName')"
                        id="campfireName"
                        name="campfireName"
                        append="Fire"
                    />
                </div>

                <div class="text-sm text-grey-200">
                    <p>Fire Naming Guidelines</p>
                    <ul class="list-disc pl-8 mt-2">
                        <li>No special characters/emojis</li>
                        <li>Avoid location specific names as we give location elsewhere on your page. Example "Dallas
                            Fire"
                        </li>
                        <li>Putting "Fire" in the name is not needed</li>
                    </ul>
                </div>
                <div>
                    <x-forms.label
                        for="geocoder-input"
                        label="Full Fire Address"
                        required
                    />
                    <div wire:ignore>
                        <div id="geocoder"></div>
                    </div>
                    <x-forms.error :error="$errors->first('mapboxPlaceName')"/>
                    <x-forms.instruction instruction="This address is specifically for the Fire. This will only be shared
                            with Wild at Heart. Attendees will only see general area."/>
                </div>

                <div class="flex items-center">
                    <x-forms.select
                        name="frequency"
                        label="Frequency"
                        required
                        :error="$errors->first('frequency')"
                        wire:model="frequency">
                        <option value="">Select</option>
                        <option value="weekly">
                            Weekly
                        </option>
                        <option value="monthly">
                            Monthly
                        </option>
                        <option value="quarterly">
                            Quarterly
                        </option>
                        <option value="twice monthly">
                            Twice Monthly
                        </option>
                        <option value="every other month">
                            Every Other Month
                        </option>
                    </x-forms.select>
                </div>

                <x-button-container>
                    @include('partials.honeypot')
                    <x-primary-button
                        type="submit"
                        class="w-full"
                        wire:click.prevent="handleStepTwo">
                        Next
                    </x-primary-button>

                    <button @click="step = 1"
                            class="text-sm py-3 text-grey-200">
                        Cancel
                    </button>

                </x-button-container>
            </div>
        </div>
    </div>

    <div class="md:w-1/2 flex items-center justify-center
    @if($step === 1)
    md:bg-cover md:bg-center md:bg-campfire
    @endif">
        <div x-show="step === 2" class="w-1/2">
            <x-campfire-card
                :title="$campfireName"
                :frequency="$frequency"
                :location="$city"
                :host-name="auth()->user()->full_name"
                :profile-image="auth()->user()->image ? asset('storage/'. auth()->user()->profilePhoto()) : gravatar(auth()->user()->email)"
            />
        </div>


    </div>
</div>
@push('head')
    <script
        src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
@endpush
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Host a Fire | {{ config('app.name') }}';
    });

    window.addEventListener('next-step', event => {
        window.scroll({
            top: 0,
            left: 0,
            behavior: 'smooth'
        });
    });

    const geocoder = new MapboxGeocoder({
        accessToken: '{{ config('services.mapbox.api_key') }}',
        types: '{{ config('services.mapbox.types') }}'
    });

    geocoder.addTo('#geocoder');

    const geocoderInput = document.querySelector('#geocoder input')
    geocoderInput.setAttribute('id', 'geocoder-input')

    geocoder.on('result', (e) => {
        @this.set('mapboxPlaceName', e.result.place_name, true)
        const placeName = e.result.place_name,
            isUSAddress = placeName.includes('United States'),
            isCanadaAddress = placeName.includes('Canada'),
            isUKAddress = placeName.includes('United Kingdom');

        if (isUSAddress) {
            const addressString = placeName,
                addressComponents = addressString.split(",").map(component => component.trim()),
                address = addressComponents[0],
                city = addressComponents[1],
                stateZip = addressComponents[2].split(" "),
                state = stateZip[0],
                zip = stateZip[1],
                country = addressComponents[3];

            @this.set('street_address', address)
            @this.set('city', city)
            @this.set('state', state)
            @this.set('zip', zip)
            @this.set('country', country)
        }

        if (isUKAddress || isCanadaAddress) {
            const addressString = placeName,
                addressComponents = addressString.split(",").map(component => component.trim()),
                address = addressComponents[0],
                city = addressComponents[2],
                postcode = addressComponents[addressComponents.length - 2].trim(),
                country = addressComponents[addressComponents.length - 1];

            @this.set('street_address', address)
            @this.set('city', city)
            @this.set('zip', postcode)
            @this.set('country', country)
        }

        @this.set('latitude', e.result.geometry.coordinates[1])
        @this.set('longitude', e.result.geometry.coordinates[0])
    });
    </script>
@endpush
