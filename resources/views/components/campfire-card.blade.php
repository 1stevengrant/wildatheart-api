@props([
    'title' => false,
    'frequency' => false,
    'location' => false,
    'profileImage' => false,
    'hostName' => false,
])

<div
    {{ $attributes->merge(['class' => 'rounded-md border border-grey-darker bg-black p-2 shadow-lg md:p-4']) }}
>
    <div class="flex items-stretch space-x-4">
        <img
            src="{{ asset('img/wah-fires-suburb-1.webp') }}"
            class="rounded-md"
            alt=""
        />
        <div class="relative flex flex-col justify-between">
            <div>
                @if ($title)
                    <p class="text-white">{{ $title }}</p>
                @endif

                @if ($location)
                    <p class="text-sm text-white">{{ $location }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <x-avatar :image="$profileImage" />
                <div class="text-sm leading-tighter text-[#BABABA]">
                    Hosted by {{ $hostName }}
                </div>
            </div>
        </div>
    </div>
</div>
