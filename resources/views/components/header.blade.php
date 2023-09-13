<div
    class="fixed left-0 right-0 top-0 z-50 border-b border-grey-darker bg-[#0B1419] p-4 md:p-6"
>
    <header class="container mx-auto flex items-center justify-between">
        <div class="mr-4 flex items-center space-x-4">
            <a href="https://wildatheart.org">
                <img
                    src="{{ asset('img/wild-at-heart-logo.svg') }}"
                    class="w-[75px] rounded-md md:w-[104px]"
                    alt=""
                />
            </a>

            <x-menu />
        </div>
        <button class="text-white md:hidden" @click="openMenu = !openMenu">
            <x-icons.menu x-show="!openMenu" x-cloak />
            <x-icons.close x-show="openMenu" x-cloak />
        </button>
        @auth
            <x-account.mobile-menu
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                x-show="openMenu"
                @click.away="openMenu = false"
                x-cloak
                class="absolute left-0 right-0 top-[75px] z-50 bg-blue-900 p-4 text-white md:hidden"
            />
        @else
            <div
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="scale-90 transform opacity-0"
                x-transition:enter-end="scale-100 transform opacity-100"
                x-transition:leave="transition duration-200 ease-in"
                x-transition:leave-start="scale-100 transform opacity-100"
                x-transition:leave-end="scale-90 transform opacity-0"
                x-cloak
                x-show="openMenu"
                @click.away="openMenu = false"
                class="absolute left-0 right-0 top-[75px] z-50 bg-blue-900 p-4 text-white md:hidden"
            >
                <x-support />
            </div>
        @endauth

        <div class="hidden items-center space-x-6 md:flex">
            <x-support />
            <x-account />
        </div>
    </header>
</div>
