@props([
    'title' => false,
    'frequency' => false,
    'location' => false,
    'profileImage' => false,
    'hostName' => false,
])

<div {{ $attributes->class(['rounded-md border-grey-darker border p-2 md:p-4 shadow-lg']) }}>
    <div class="flex items-stretch space-x-4">
        <img src="{{ asset('img/wah-fires-suburb-1.webp') }}"
             class="rounded-md"
             alt="">
        <div class="flex flex-col justify-between relative">

            <div>
                <div class="text-grey-200 text-sm capitalize">
                    {{ $frequency }}
                </div>
                @if($title)
                    <p class="text-white">{{ $title }} Fire</p>
                @endif
                <p class="text-white text-sm">{{ $location }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <img src="{{ $profileImage }}"
                     class="rounded-full w-6 h-6 object-fill object-fit"
                     alt="">
                <div class="text-[#BABABA] text-sm leading-tighter">Hosted
                    by {{ $hostName }}</div>
            </div>
        </div>
    </div>
</div>
