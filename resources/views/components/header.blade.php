<div class="bg-[#0B1419] fixed top-0 left-0 right-0 border-grey-darker border-b p-4 md:p-6 z-50">
    <header class="flex justify-between items-center container mx-auto">
        <div class="flex items-center space-x-4 mr-4">
            <a href="https://wildatheart.org">
                <img src="{{ asset('img/wild-at-heart-logo.svg') }}"
                     class="w-[75px] md:w-[104px] rounded-md"
                     alt="">
            </a>
            <x-menu />
        </div>
        <div class="hidden md:flex items-center space-x-6">
            <x-support />
            <x-account />
        </div>
    </header>
</div>
