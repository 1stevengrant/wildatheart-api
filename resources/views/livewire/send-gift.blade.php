<div class="h-screen text-white md:flex">
    <div
        class="relative mt-8 flex items-center justify-center overflow-auto px-8 md:w-1/2"
    >
        @if (! $submitted)
            <div class="h-full max-w-lg">
                <x-type.registration-h1>
                    Huzzah!
                    <br />
                    Your Fire has been registered.
                </x-type.registration-h1>

                <p class="mt-8">
                    We'd like to send you a fire-starter kit. Where would you
                    like us to send it?
                </p>

                <div class="space-y-8">
                    <div class="mt-8">
                        <div>
                            <x-forms.label
                                for="email"
                                label="Mailing Address"
                                class="mt-8" />
                        </div>

                        <div class="space-y-4">
                            <x-forms.select
                                label="Country"
                                wire:model="country"
                                required
                                :error="$errors->first('country')"
                                name="country"
                            >
                                <option value="">Select Country</option>
                                @foreach (getCountries() as $key => $country)
                                    <option value="{{ $key }}">
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <div class="grid gap-4 md:grid-cols-2">
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

                            <div class="grid gap-4 md:grid-cols-3">
                                <x-forms.input
                                    label="City"
                                    required
                                    name="city"
                                    :error="$errors->first('city')"
                                    wire:model="city"
                                />

                                <x-forms.select
                                    name="state"
                                    label="State"
                                    required
                                    :error="$errors->first('state')"
                                    wire:model="state"
                                >
                                    <option value="">Select</option>
                                    <option value="-">Not Applicable</option>
                                    @foreach (\App\Models\State::all() as $state)
                                        <option value="{{ $state->code }}">
                                            {{ $state->name }}
                                        </option>
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
                        <x-primary-button class="w-full" wire:click="handle">
                            Send me my gift
                        </x-primary-button>
                        <a
                            href="{{ route('home') }}"
                            class="py-3 text-sm text-grey-200"
                        >
                            Cancel
                        </a>
                    </x-button-container>
                </div>
            </div>
        @else
            <div class="max-w-lg">
                <x-type.registration-h1>
                    Yay!
                    <br />
                    Your gift is on its way
                </x-type.registration-h1>

                <x-button-container class="mt-8">
                    <a
                        href="{{ route('view.campfire', $campfire) }}"
                        class="rounded-md border border-transparent bg-orange px-4 py-2 text-center font-medium text-white transition duration-150 ease-in-out hover:bg-transparent-orange disabled:cursor-not-allowed disabled:bg-grey-200 disabled:text-grey-darker"
                    >
                        {{ __('Visit your Fire listing') }}
                    </a>
                </x-button-container>
            </div>
        @endif
    </div>
    <div class="hidden items-center justify-center md:flex md:w-1/2">
        <div
            class="flex h-[511px] w-[373px] flex-col items-center justify-center rounded-lg text-white"
        >
            <img src="{{ asset('img/fires-gift-image-v2.webp') }}" alt="" />
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title = "Claim Your Gift 🥳 | {{ config('app.name') }}";

            window.addEventListener("next-step", (event) => {
                window.scroll({
                    top: 0,
                    left: 0,
                    behavior: "smooth",
                });
            });
        });
    </script>
@endpush
