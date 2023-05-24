<footer class="fixed bottom-0 left-0 z-50 w-full bg-black border-t border-grey-dark p-4 md:hidden">
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

        <x-footer-link
            href="{{ route('faqs') }}">
            <x-slot:icon>
                <x-icons.questions/>
            </x-slot:icon>
            {{ __('FAQs') }}
        </x-footer-link>

        @auth
            <div class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 group">
                <div class="w-12 h-12 bg-white flex items-center justify-center rounded-full">
                    <img src="{{ Gravatar::get('hello@digitalevangelist.net') }}"
                         class="rounded-full w-9 h-9"
                         alt="">
                </div>
            </div>
        @endauth
        @guest
            <x-footer-link
                href="{{ route('faqs') }}">
                <x-slot:icon>
                    <x-icons.user class="w-[24px] h-[24px] text-white fill-current stroke-current"/>
                </x-slot:icon>
                {{ __('Login') }}
            </x-footer-link>
        @endguest
    </div>
</footer>
