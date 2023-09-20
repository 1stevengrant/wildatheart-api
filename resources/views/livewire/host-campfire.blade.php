<div
    class="h-screen text-white md:flex"
    x-data="{ step: @entangle('step') }"
>
    <div
        x-show="step === 1"
        class="relative mt-8 flex items-center justify-center overflow-auto px-8 md:w-1/2"
    >
        <div class="mx-auto h-full pb-8 md:w-2/3 md:pb-0">
            <x-profile-stepper :step="$step" />

            <x-type.registration-h1 class="mt-12">
                We're stoked you want to start a Fire! Just a few questions.
            </x-type.registration-h1>

            <p class="mt-8">
                One of the most important aspects of our Fires are the hosts
                that commit to them.
            </p>

            <div class="mt-6 space-y-8">
                <div class="grid gap-8 md:grid-cols-2">
                    <x-forms.input
                        maxlength="50"
                        name="first_name"
                        id="first_name"
                        required
                        wire:model.defer="firstName"
                        :error="$errors->first('firstName')"
                        label="First Name"
                    />

                    <x-forms.input
                        name="last_name"
                        maxlength="50"
                        id="last_name"
                        required
                        wire:model.defer="lastName"
                        :error="$errors->first('lastName')"
                        label="Last Name"
                    />
                    <div class="col-span-2">
                        <div>
                            <x-forms.label
                                for="email"
                                maxlength="255"
                                label="Email"
                            />
                            <div>{{ auth()->user()->email }}</div>
                        </div>
                    </div>
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
                                    wire:ignore
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
                        <x-forms.label
                            for="file"
                            label="Profile Photo"
                            class="mt-2"
                        />
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
                    <x-forms.label
                        for="emailUpdates"
                        label="Updates & Resources"
                    />
                    <div
                        class="flex flex-col space-y-4 md:flex-row md:items-center md:space-x-4 md:space-y-0"
                    >
                        <div class="flex items-center rounded-full bg-form">
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', true)"
                                class="@if ($emailUpdates) border border-white text-white rounded-l-full @endif w-1/2 px-10 py-3 text-sm font-semibold"
                            >
                                Yes
                            </button>
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', false)"
                                class="@if (!$emailUpdates) border border-white text-white rounded-r-full @endif w-1/2 px-10 py-3 text-sm font-semibold"
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
                        wire:click.prevent="handleStepOne"
                    >
                        Next
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
    </div>

    <div
        x-show="step === 2"
        class="mt-8 flex items-center justify-center overflow-auto px-8 md:w-1/2"
    >
        <div class="relative mx-auto h-full md:w-2/3">
            <x-profile-stepper :step="$step" />

            <x-type.registration-h1 class="my-12">
                Tell us about your Fire
            </x-type.registration-h1>

            <div x-show="step === 2" class="md:hidden">
                <x-campfire-card
                    :title="$campfireName"
                    :frequency="$frequency"
                    :location="$location"
                    :host-name="auth()->user()->full_name"
                    :profile-image="
                        auth()
                            ->user()
                            ->profilePhoto()
                    "
                />
            </div>

            <div class="mt-8 space-y-4">
                <div class="flex items-center space-x-4">
                    <x-forms.input
                        wire:model="campfireName"
                        label="Name Your Fire"
                        required
                        maxlength="50"
                        instruction="This is the name that will be displayed on your Fire page."
                        :error="$errors->first('campfireName')"
                        id="campfireName"
                        name="campfireName"
                        append="Fire"
                    />
                </div>

                <div class="text-sm text-grey-200">
                    <p>Fire Naming Guidelines</p>
                    <ul class="mt-2 list-disc pl-8">
                        <li>No special characters/emojis</li>
                        <li>
                            Avoid location specific names as we give location
                            elsewhere on your page. Example "Dallas Fire"
                        </li>
                        <li>Putting "Fire" in the name is not needed</li>
                    </ul>
                </div>

                <x-map type="Set" :mapboxError="$mapboxError" />

                <x-button-container>
                    @include('partials.honeypot')
                    <x-primary-button
                        type="submit"
                        class="mt-8 w-full"
                        :disabled="! empty($mapboxError)"
                        wire:click.prevent="handleStepTwo"
                    >
                        Next
                    </x-primary-button>

                    @if ($step == 2)
                        <a
                            href="{{ route('my.profile') }}"
                            class="py-3 text-sm text-grey-200"
                        >
                            Cancel
                        </a>
                    @else
                        <button
                            @click="step = 1"
                            class="py-3 text-sm text-grey-200"
                        >
                            Cancel
                        </button>
                    @endif
                </x-button-container>
            </div>
        </div>
    </div>

    <div
        class="@if ($step === 1) md:bg-cover md:bg-center md:bg-campfire @endif flex items-center justify-center md:w-1/2"
    >
        <div x-show="step === 2" class="hidden md:block">
            <x-campfire-card
                :title="$campfireName . ' Fire'"
                :frequency="$frequency"
                :location="$city ?? $locality ?? $district ?? $region ?? $country"
                :host-name="auth()->user()->full_name"
                :profile-image="
                    auth()
                        ->user()
                        ->profilePhoto()
                "
            />
        </div>
    </div>
</div>
@push('scripts')
    <script>
        window.addEventListener("next-step", () => {
            setTimeout(() => {
                window.scroll({
                    top: 0,
                    left: 0,
                    behavior: "smooth",
                });
            }, 10);
        });
    </script>

    @include('js.update-map')
@endpush
