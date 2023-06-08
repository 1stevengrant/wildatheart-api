<footer id="footer"
        class="fixed bottom-0 left-0 z-50 w-full bg-black border-t border-grey-dark p-4 md:hidden">
    <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
        <x-footer-link
            href="{{ route('home') }}">
            <x-slot:icon>
                <x-icons.fire/>
            </x-slot:icon>
            {{ __('Fires') }}

        </x-footer-link>

        <x-footer-link
            href="{{ route('host') }}">
            <x-slot:icon>
                <x-icons.hand-wave/>
            </x-slot:icon>
            {{ __('Host') }}
        </x-footer-link>

        @auth
            <form class="inline-flex flex-col items-center justify-center px-5 group"
                  method="POST"
                  action="{{ route('logout') }}">
                @csrf
                <img src="{{ auth()->user()->profilePhoto() }}"
                     class="w-6 h-6 rounded-full"
                     alt="">
                <button onclick="event.preventDefault(); this.closest('form').submit();"
                        class="text-sm text-white">
                    {{ __('Logout') }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="inline-flex flex-col items-center justify-center px-5 group">
                <x-icons.user/>
                <span class="text-sm text-white">
                    {{ __('Login') }}
                </span>
            </a>
        @endauth
    </div>
</footer>
