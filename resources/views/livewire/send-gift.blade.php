<div class="md:flex h-screen text-white">
    <div class="md:w-1/2 mt-8 px-8 flex items-center justify-center relative">

        @if(!$submitted)
        <div class="max-w-lg">
            <x-type.registration-h1>
                Huzzah! <br>
               Your Fire has been registered.
            </x-type.registration-h1>

            <p class="mt-8">We'd like to send you a fire-starter kit. Where would you like us to send it?</p>

            <div class="space-y-8">

                <div class="mt-8">
                    <div>
                        <x-forms.label
                            for="email"
                            label="Mailing Address"
                            class="mt-8"/>
                    </div>

                    <div class="space-y-4">
                        <x-forms.select
                            label="Country"
                            wire:model="country"
                            required
                            :error="$errors->first('country')"
                            name="country">
                            <option value="">Select Country</option>
                            @foreach(getCountries() as $key => $country)
                                <option value="{{ $key }}">{{ $country }}</option>
                            @endforeach
                        </x-forms.select>

                        <div class="grid md:grid-cols-2 gap-4">
                            <x-forms.input
                                label="First Name"
                                required
                                name="first_name"
                                wire:model="firstName"
                                :error="$errors->first('firstName')"
                            />

                            <x-forms.input
                                label="Last Name"
                                required
                                :error="$errors->first('lastName')"
                                name="last_name"
                                wire:model="lastName"
                            />
                        </div>

                        <x-forms.input
                            label="Company"
                            name="company"
                            :error="$errors->first('company')"
                            wire:model="company"
                            placeholder="(optional)"
                        />

                        <x-forms.input
                            label="Address"
                            required
                            name="address1"
                            :error="$errors->first('address1')"
                            wire:model="address1"
                        />

                        <x-forms.input
                            label=""
                            name="address2"
                            wire:model="address2"
                            :error="$errors->first('address2')"
                            placeholder="Apartment, suite, etc. (optional)"
                        />

                        <div class="grid md:grid-cols-3 gap-4">
                            <x-forms.input
                                label="City"
                                required
                                name="city"
                                :error="$errors->first('city')"
                                wire:model="city"
                            />
                            <x-forms.select
                                label="State"
                                wire:model="state"
                                :error="$errors->first('state')"
                                name="state">
                                <option value="">Select State</option>
                                @foreach(\App\Models\State::all() as $state)
                                    <option value="{{ $state->name }}">{{ $state->name }}</option>
                                @endforeach
                            </x-forms.select>

                            <x-forms.input
                                label="Zip/Postcode"
                                name="zip"
                                :error="$errors->first('zip')"
                                wire:model="zip"
                                required
                            />
                        </div>

                        <x-forms.input
                            label="Phone"
                            name="phone"
                            wire:model="phone"
                            :error="$errors->first('phone')"
                        />

                    </div>

                </div>

                <x-button-container>
                    <x-primary-button
                        class="w-full"
                        wire:click="handle">
                        Send me my gift
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

                <x-button-container class="mt-8">
                    <a href="{{ route('view.campfire', $campfire) }}" class="px-4 py-2 border
border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition
ease-in-out duration-150 disabled:bg-grey-200 disabled:text-grey-darker disabled:cursor-not-allowed">
                        {{ __('Visit your Fire listing') }}
                    </a>
                </x-button-container>

            </div>
        @endif
    </div>
    <div class="hidden md:w-1/2 md:flex items-center justify-center ">
        <div class="w-[373px] h-[511px] bg-grey-dark text-white flex flex-col items-center justify-center rounded-lg">
            <img src="{{ asset('img/fires-gift-image-v2.webp') }}" alt="">
        </div>
    </div>
</div>
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Claim Your Gift 🥳 | {{ config('app.name') }}';
    });
    </script>
@endpush
