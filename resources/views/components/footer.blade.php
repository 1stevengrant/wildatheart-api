<footer
    id="footer"
    class="fixed bottom-0 left-0 z-50 w-full border-t border-grey-dark bg-black p-4 md:hidden"
>
    <div
        class="mx-auto flex h-full max-w-lg items-center justify-center font-medium"
    >
        <x-footer-link href="{{ route('home') }}">
            <x-slot name="icon">
                <x-icons.fire />
            </x-slot>
            {{ __('Fires') }}
        </x-footer-link>

        <x-footer-link href="{{ route('host') }}">
            <x-slot name="icon">
                <x-icons.hand-wave />
            </x-slot>
            {{ __('Host') }}
        </x-footer-link>

        @auth
            <a
                class="group inline-flex flex-col items-center justify-center px-5"
                href="{{ route('my.profile') }}"
            >
                <x-avatar :image="auth()->user()->profilePhoto()" />
                <span class="text-sm text-white">
                    {{ __('My Profile') }}
                </span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="group inline-flex flex-col items-center justify-center px-5"
            >
                <x-icons.user />
                <span class="text-sm text-white">
                    {{ __('Login') }}
                </span>
            </a>
        @endauth

        <button class="flex flex-col items-center" onclick="Livewire.emit('modal.open', 'report-issue')">
            <x-icons.questions/>
            <span class="text-sm text-white">
                {{ __('Feedback') }}
            </span>
        </button>
    </div>
</footer>
