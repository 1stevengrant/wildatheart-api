<div class="h-screen flex flex-col justify-center items-center p-6">
    <div class="md:flex items-center md:space-x-4 md:w-[1000px]">
        <div class="md:w-1/2 text-grey-200">

            <x-type.registration-h1>
                Great! One Last Step{{ auth()->user()->given_name ? ' ' . auth()->user()->given_name : '.' }}
            </x-type.registration-h1>


            <p class="my-6">Please click on the link in the email we just sent you to finalize your listing.</p>

            <x-button-container class="mt-8">
                <button class="text-sm text-white border-b border-b-white hover:border-transparent"
                    wire:click="resendEmail">
                    Resend confirmation email
                </button>
            </x-button-container>
        </div>
        <div class="md:w-1/2 mt-8 mt:mt-0">
            <img src="{{ asset('img/fires-gift-image-v2.webp') }}" alt="">
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function() {
            document.title = 'Confirm Your Email | {{ config('app.name') }}';
        });
    </script>
@endpush
