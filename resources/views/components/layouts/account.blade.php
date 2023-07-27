@props([
    'campfires',
])

<div class="mt-[75px] text-white md:mt-[107px]">
    <div class="md:flex md:flex-row">
        <x-account.menu
            class="hidden flex-col items-center space-y-6 border-r border-r-grey-darker p-4 md:flex md:w-1/6 md:px-7 md:py-6"
        />
        <div class="mb-24 p-4 md:w-5/6 md:px-7 md:py-6">
            {{ $slot }}
        </div>
    </div>
</div>
