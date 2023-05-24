<div class="border-grey-darker border-b p-4 md:p-6">
    <header class="flex justify-between items-center container mx-auto">
        <div class="flex items-center space-x-4 mr-4">
            <a href="/">
                <img src="{{ asset('img/wild-at-heart-logo.svg') }}"
                     class="w-[75px] md:w-[104px] rounded-md"
                     alt="">
            </a>
            <x-menu />
        </div>
        <x-search />
        <div class="hidden md:flex items-center space-x-6">
            <x-support />
            <x-account />
        </div>
    </header>
</div>
