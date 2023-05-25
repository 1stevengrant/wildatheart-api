<div class="md:flex h-screen text-white relative">
    @if($step === 1)
        <div class="md:w-1/2 mt-8 px-8">
            <div class="grid grid-cols-3 gap-2 top-8 left-8 w-full">
                <div class="h-3 border border-white rounded-full "></div>
                <div class="h-3  border border-white rounded-full "></div>
                <div class="h-3  border border-white rounded-full "></div>
            </div>

            <form wire:submit.prevent="handleStepOne">

                <h1 class="text-[39px] leading-[44px]">Congrats! Just a few more questions.</h1>

                <p>One of the most important aspects of our Fires are the hosts that commit to them. </p>

                <x-forms.input
                    name="phone"
                    id="phone"
                    instruction="Your phone number will only be used for epic memes and cat photos."
                    label="Phone Number"/>

                <div class="flex items-center space-x-4 "
                     x-data="dropFileComponent()">
                    <div
                        class="py-6 w-[134px] h-[134px] rounded-full flex flex-col justify-center items-center bg-[#D9D9D9]
                flex-shrink-0"
                        x-bind:class="droppingFile ? 'bg-gray-400 border-gray-500' : 'border-gray-500 bg-gray-200'"
                        x-on:drop="droppingFile = false"
                        x-on:drop.prevent="handleFileDrop($event)"
                        x-on:dragover.prevent="droppingFile = true"
                        x-on:dragleave.prevent="droppingFile = false">
                        <x-icons.camera/>
                        <div class="mt-1" wire:loading.flex wire.target="photos">
                            <x-icons.spinner/>
                            <div>Processing Files</div>
                        </div>
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold text-[#B8B6B6]">Profile Photo</p>
                        <p class="mt-2 text-[#B8B6B6]">Your photo will only be used to help your community identify your
                            fire.
                            WaH will never
                            use your
                            photo for anything other than your Fire page.</p>
                    </div>
                </div>

                <div class="text-[#B8B6B6] text-sm">
                    <p class="font-semibold">Email Updates & Resources</p>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center rounded-full bg-[#171513] border-grey-darker border ">
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', true)"
                                class="px-4 py-6 @if($emailUpdates) border border-white rounded-l-full @endif"
                            >
                                Yes
                            </button>
                            <button
                                type="button"
                                wire:click.prevent="$set('emailUpdates', false)"
                                class="px-4 py-6 @if(!$emailUpdates) border border-white rounded-r-full @endif"
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

            </form>
        </div>
    @endif
    <div class="md:w-1/2 bg-cover bg-campfire">
        <div></div>
    </div>
</div>
@push('scripts')
    <script>
    function dropFileComponent() {
        return {
            droppingFile: false,
            handleFileDrop(e) {
                if (event.dataTransfer.files.length > 0) {
                    const files = e.dataTransfer.files;
                    @this.uploadMultiple('photos', files,
                        (uploadedFilename) => {
                        }, () => {
                        }, (event) => {
                        }
                    )
                }
            }
        };
    }

    document.addEventListener('livewire:load', function () {
        document.title = 'Host a Fire | {{ config('app.name') }}';
    });
    </script>
@endpush
