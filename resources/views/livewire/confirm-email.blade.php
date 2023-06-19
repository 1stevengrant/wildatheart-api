<div class="flex h-screen flex-col items-center justify-center p-6">
    <div class="items-center md:flex md:w-[1000px] md:space-x-4">
        <div class="text-grey-200 md:w-1/2">
            <x-type.registration-h1>
                Great! One Last
                Step{{ auth()->user()->given_name ? ' ' . auth()->user()->given_name : '.' }}
            </x-type.registration-h1>

            <p class="my-6">
                Please click on the link in the email we just sent you to
                finalize your listing.
            </p>

            <x-button-container class="mt-8">
                <button
                    class="border-b border-b-white text-sm text-white hover:border-transparent"
                    wire:click="resendEmail"
                >
                    Resend confirmation email
                </button>
            </x-button-container>
        </div>
        <div class="mt:mt-0 mt-8 md:w-1/2">
            <img
                src="{{ asset('img/fires-gift-image-v2.webp') }}"
                alt=""
                class="hidden md:block"
            />
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title = "Confirm Your Email | {{ config('app.name') }}";
        });
    </script>
@endpush
