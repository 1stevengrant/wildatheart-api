<div class="h-screen flex flex-col justify-center items-center p-6">
    <div class="md:flex items-center md:space-x-4 md:w-[1000px]">
        <div class="md:w-1/2 text-grey-200">

            <h1 class="text-white text-[39px]">
                Important!
            </h1>

            <p class="mt-[11px]">
                You’ve successfully created your Fire. Congrats!
            </p>

            <p class="mt-6">
                We’ve sent a confirmation email with all your information to
                {{ auth()->user()->email }}.
            </p>

            <x-button-container class="mt-8">
                <button
                    class="w-full text-sm"
                    wire:click="resendEmail">
                    I didn't receive an email
                </button>
            </x-button-container>
        </div>
        <div class="md:w-1/2 mt-8 mt:mt-0">
            <img src="{{ asset('img/fires-gift-image.webp') }}"
                 class=""
                 alt="">
        </div>
    </div>
</div>

@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Confirm Your Email | {{ config('app.name') }}';
    });
    </script>
@endpush
