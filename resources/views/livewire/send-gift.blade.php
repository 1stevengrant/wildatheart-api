<div class="md:flex h-screen text-white">
    <div class="md:w-1/2 mt-8 px-8 flex items-center justify-center relative">

        @if(!$submitted)
        <div class="max-w-lg">
            <x-type.registration-h1>
                Huzzah! <br>
                Your email is confirmed, and we have a gift for you
            </x-type.registration-h1>
            <p class="text-sm text-white font-bold underline mt-4">Promise we won’t spam you</p>

            <div class="space-y-8">

                <div x-data="{ open:false }"
                     class="mt-8">
                    <div>
                        <x-forms.label
                            for="email"
                            label="Mailing Address"
                            class="mt-8"/>

                        <div class="flex items-center rounded-full bg-[#171513]">
                            <button
                                type="button"
                                @click="open = false; $wire.set('differentMailingAddress', false)"
                                class="px-10 py-3 text-sm font-semibold @if(!$differentMailingAddress) border border-white text-white
                            rounded-l-full
                                 @endif"
                            >
                                Same as Fire Address
                            </button>
                            <button
                                type="button"
                                @click="open = true; $wire.set('differentMailingAddress', true)"
                                class="px-10 py-3 text-sm font-semibold @if($differentMailingAddress) border border-white text-white
                            rounded-r-full
                                @endif"
                            >
                                Different from Fire Address
                            </button>
                        </div>
                    </div>


                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90"
                         class="mt-8">
                        <x-forms.label
                            for="geocoder-input"
                            label="Address Lookup"
                            required/>
                        <div wire:ignore>
                            <div id="geocoder"></div>
                        </div>
                        <x-forms.error :error="$errors->first('mapboxPlaceName')"/>
                        <x-forms.instruction instruction="This address will be used to send your free gift."/>
                    </div>
                </div>

                <x-button-container>
                    <x-primary-button
                        id="confetti-button"
                        class="w-full"
                        wire:click="handle">
                        Claim Your Free Gift
                    </x-primary-button>
                    <a href="{{ route('home') }}" class="text-sm py-3 text-grey-200">
                        Cancel
                    </a>
                </x-button-container>
            </div>

        </div>
        @else
            <div class="max-w-lg">
                <x-type.registration-h1>
                    Yay! <br>
                    Your gift is on its way
                </x-type.registration-h1>
            </div>
        @endif
    </div>
    <div class="hidden md:w-1/2 md:flex items-center justify-center ">
        <div class="w-[373px] h-[511px] bg-grey-dark text-white flex flex-col items-center justify-center rounded-lg">
            <div>Image of workbook etc.</div>
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
        document.title = 'Claim Your Free Gift 🥳 | {{ config('app.name') }}';
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
    });
    </script>
@endpush
