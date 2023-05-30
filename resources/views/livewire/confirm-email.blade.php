<div class="h-screen flex flex-col justify-center items-center">
    <div class="text-[#B8B6B6] max-w-md">

        <h1 class="text-white text-[39px]">
            Confirm your email!
        </h1>

        <p class="mt-[11px]">
            You’ve successfully created your Fire. Congrats!
        </p>

        <p class="mt-6">
            We’ve sent a confirmation email with all your information to
            {{ auth()->user()->email }}.
        </p>

        <x-button-container class="mt-8">
            <x-primary-button
                class="w-full"
                wire:click="resendEmail">
                I didn't receive an email
            </x-primary-button>
        </x-button-container>
    </div>
</div>

@push('scripts')
    <script>
    </script>
@endpush
