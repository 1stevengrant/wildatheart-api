@props([
    'title' => false,
    'frequency' => false,
    'location' => false,
    'profileImage' => false,
    'hostName' => false,
])

<div
    {{ $attributes->class(['rounded-md border border-grey-darker p-2 shadow-lg md:p-4']) }}
>
    <div class="flex items-stretch space-x-4">
        <img
            src="{{ asset('img/wah-fires-suburb-1.webp') }}"
            class="rounded-md"
            alt=""
        />
        <div class="relative flex flex-col justify-between">
            <div>
                <div class="text-sm capitalize text-grey-200">
                    {{ $frequency }}
                </div>
                @if ($title)
                    <p class="text-white">{{ $title }}</p>
                @endif

                <p class="text-sm text-white">{{ $location }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <img
                    src="{{ $profileImage }}"
                    class="object-fit h-6 w-6 rounded-full object-fill"
                    alt=""
                />
                <div class="text-sm leading-tighter text-[#BABABA]">
                    Hosted by {{ $hostName }}
                </div>
            </div>
        </div>
    </div>
</div>
