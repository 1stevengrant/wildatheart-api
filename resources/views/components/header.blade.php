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
        <div class="hidden items-center space-x-6 md:flex">
            <x-support />
            <x-account />
        </div>
    </header>
</div>
