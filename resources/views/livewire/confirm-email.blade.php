<div class="h-screen flex flex-col justify-center items-center p-6">
    <div class="md:flex items-center md:space-x-4 md:w-[1000px]">
        <div class="md:w-1/2 text-grey-200">

            <h1 class="text-white text-[39px]">
                Important, {{ auth()->user()->given_name }}!
            </h1>

            <div class="space-y-6 mt-12">
                <p>You’ve successfully created your Fire. Congrats!</p>

                <p>The lives of many rest in the courage of a few.</p>

                <p>You’re one of the few.</p>

                <p>Well done!</p>

                <p>Way to risk.</p>

                <p>Way to love.</p>

                <p>We need more men like you in this hour.</p>

                <p>Make sure you confirm your email. We want to fuel your fire with a gift from our team. We are
                    honored to stand alongside you in this mission for the hearts of men around the world.</p>

                <p>
                    We’ve sent a confirmation email with all your information to
                    {{ auth()->user()->email }}.
                </p>
            </div>

            <x-button-container class="mt-8">
                <button
                    class="text-sm text-orange border-b border-b-orange hover:border-transparent"
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
