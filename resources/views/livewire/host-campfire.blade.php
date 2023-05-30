<div class="md:flex h-screen text-white" x-data="{ step: {{ $step }}}">

    <div x-show="step === 1"

         class="md:w-1/2 mt-8 px-8 flex items-center justify-center relative">

        <form wire:submit.prevent="handleStepOne"
              class="md:w-2/3 mx-auto pb-8 md:pb-0">

            <x-type.registration-h1>
                Congrats! Just a few more questions.
            </x-type.registration-h1>

            <p class="mt-8">One of the most important aspects of our Fires are the hosts that commit to them. </p>

            <div class="space-y-8 mt-6">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <x-forms.country-phone-codes
                            label="Country Code"
                            name="countryCode"
                            wire:model="countryCode"
                            instruction="Select your country code"
                        />
                    </div>
                    <div>
                        <x-forms.input
                            name="phone"
                            id="phone"
                            wire:model.defer="phone"
                            :error="$errors->first('phone')"
                            instruction="Your phone number will only be used for epic memes and cat photos."
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
                        <label for="file"
                               class="w-[134px] h-[134px] rounded-full bg-[#D9D9D9] flex justify-center
                        items-center mx-auto focus:ring-orange focus:ring">
                            <x-icons.camera/>
                        </label>

                    </div>
                    <div class="text-sm">
                        <x-forms.label for="file"
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

                <div class="text-[#B8B6B6] text-sm">
                    <x-forms.label
                        for="emailUpdates"
                        label="Email Updates & Resources"/>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center rounded-full bg-[#171513]">
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', true)"
                                class="px-10 py-3 text-sm font-semibold @if($emailUpdates) border border-white text-white
                            rounded-l-full
                            @endif"
                            >
                                Yes
                            </button>
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', false)"
                                class="px-10 py-3 text-sm font-semibold @if(!$emailUpdates) border border-white text-white
                            rounded-r-full
                                    @endif"
                            >
                                No
                            </button>
                        </div>
                        <div>
                            I would like to receive email updates & resources from Wild at Heart
                        </div>
                    </div>
                </div>

                <x-button-container>
                    <x-primary-button class="w-full">
                        Next
                    </x-primary-button>

                    <a href="{{ route('home') }}" class="text-sm py-3 text-[#B8B6B6]">
                        Cancel
                    </a>

                </x-button-container>
            </div>
        </form>
    </div>

    <div class="md:w-1/2 mt-8 px-8 flex items-center justify-center"
         x-show="step === 2">

        <form wire:submit.prevent="handleStepTwo"
              class="md:w-2/3 mx-auto relative">

            <x-type.registration-h1>
                Now the Fire
            </x-type.registration-h1>

            <div class="space-y-10">
                <x-forms.input
                    wire:model="campfireName"
                    label="Name Your Fire"
                    instruction="This is the name that will be displayed on your Fire page."
                    required
                    id="campfireName"
                    name="campfireName"
                />

                <div>
                    <x-forms.label for="geocoder-input" label="Full Fire Address" required/>
                    <div wire:ignore>
                        <div id="geocoder"></div>
                    </div>
                    <x-forms.error :error="$errors->first('mapboxPlaceName')"/>
                    <x-forms.instruction instruction="This address is specifically for the Fire. This will
                            only be shared
                            with Wild at Heart.
                            Attendees will
                            only see general area."/>
                </div>

                <div>
                    <x-forms.label for="public" label="Public or Private Fire" required/>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center rounded-full bg-[#171513] border border-grey-darker">
                            <button
                                type="button"
                                wire:click="$set('isPrivate', false)"
                                class="px-10 py-3 text-sm font-semibold @if(!$isPrivate) border border-white text-white
                            rounded-l-full
                            @endif"
                            >
                                Public
                            </button>
                            <button
                                type="button"
                                wire:click="$set('isPrivate', true)"
                                class="px-10 py-3 text-sm font-semibold @if($isPrivate) border border-white text-white
                            rounded-r-full
                            @endif"
                            >
                                Private
                            </button>
                        </div>
                    </div>

                    <x-forms.instruction instruction="Public events will be discoverable on the listing view. Private events will only be accessible via
                            your sharing link."/>
                </div>

                <div class="flex items-center">
                    <x-forms.select
                        name="frequency"
                        label="Occurs"
                        required
                        :error="$errors->first('frequency')"
                        wire:model="frequency">
                        <option value="">Select</option>
                        @foreach(\App\Enums\CampfireFrequency::values() as $frequency)
                            <option value="{{ $frequency }}">
                                {{ $frequency }}
                            </option>
                        @endforeach
                    </x-forms.select>
                </div>

                <div class="">
                    <x-forms.label for="" label="Outdoor or Indoor" required/>
                    <div class="flex items-center mt-2">
                        <div class="flex items-center rounded-full bg-[#171513] border border-grey-darker">
                            <button
                                type="button"
                                wire:click="$set('isIndoor', false)"
                                class="px-10 py-3 text-sm font-semibold @if(!$isIndoor) border-white border text-white
                            rounded-l-full
                            @endif"
                            >
                                Outdoor
                            </button>
                            <button
                                type="button"
                                wire:click="$set('isIndoor', true)"
                                class="px-10 py-3 text-sm font-semibold @if($isIndoor) border-white border text-white
                            rounded-r-full
                            @endif"
                            >
                                Indoor
                            </button>
                        </div>
                    </div>
                    <x-forms.instruction instruction="We will let your guests know to dress accordingly."/>
                </div>

                <x-button-container>
                    <x-primary-button class="w-full">
                        Next
                    </x-primary-button>

                    <a href="{{ route('home') }}" class="text-sm py-3 text-[#B8B6B6]">
                        Cancel
                    </a>

                </x-button-container>
            </div>


        </form>
    </div>

    <div class="md:w-1/2 flex items-center justify-center @if($step === 1) bg-cover bg-campfire @endif">
        @if($step === 2)
            <div class="rounded-md border-grey-darker border p-5 w-1/2 shadow-lg">
                <div class="flex items-stretch space-x-4">
                    <img src="{{ asset('img/campfire.png') }}"
                         class="rounded-md"
                         alt="">
                    <div class="flex flex-col justify-between relative">
                        <div>
                            @if($campfireName)
                                <p>{{ $campfireName }}</p>
                            @else
                                <p>Your Fire Name</p>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ gravatar(auth()->user()->email, 'campfire-profile') }}"
                                 class="rounded-full"
                                 alt="">
                            <div class="text-[#BABABA] text-sm">Hosted by {{ auth()->user()->full_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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

    const geocoder = new MapboxGeocoder({
        accessToken: '{{ config('services.mapbox.api_key') }}',
        types: 'address,country,region,place,postcode,locality,neighborhood'
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

            @this.set('address', address, true)
            @this.set('city', city, true)
            @this.set('state', state, true)
            @this.set('zip', zip, true)
            @this.set('country', country, true)
            @this.set('address', address, true)
        }

        if (isUKAddress || isCanadaAddress) {
            const addressString = placeName,
                addressComponents = addressString.split(",").map(component => component.trim()),
                address = addressComponents[0],
                city = addressComponents[1],
                postcode = addressComponents[addressComponents.length - 2].trim(),
                country = addressComponents[addressComponents.length - 1];

            @this.set('address', address, true)
            @this.set('city', city, true)
            @this.set('zip', postcode, true)
            @this.set('country', country, true)
            @this.set('address', address, true)
        }

        @this.set('latitude', e.result.geometry.coordinates[0], true)
        @this.set('longitude', e.result.geometry.coordinates[1], true)
    });
    </script>
@endpush
