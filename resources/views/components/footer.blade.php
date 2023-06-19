<footer
    id="footer"
    class="fixed bottom-0 left-0 z-50 w-full border-t border-grey-dark bg-black p-4 md:hidden"
>
    <div class="mx-auto grid h-full max-w-lg grid-cols-4 font-medium">
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
            <form
                class="group inline-flex flex-col items-center justify-center px-5"
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf
                <img
                    src="{{ auth()->user()->profilePhoto() }}"
                    class="h-6 w-6 rounded-full"
                    alt=""
                />
                <button
                    onclick="event.preventDefault(); this.closest('form').submit();"
                    class="text-sm text-white"
                >
                    {{ __('Logout') }}
                </button>
            </form>
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
    </div>
</footer>
