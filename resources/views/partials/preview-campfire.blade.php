<div
    class="hidden rounded-md border border-grey-darker p-5 shadow-lg md:block md:w-1/2"
>
    <div class="flex items-stretch space-x-4">
        <img
            src="{{ asset('img/wah-fires-suburb-1.webp') }}"
            class="rounded-md"
            alt=""
        />
        <div class="relative flex flex-col justify-between">
            <div>
                <p class="text-xs capitalize text-grey-200">
                    {{ $frequency }}
                </p>

                @if ($campfireName)
                    <p>{{ $campfireName }} Fire</p>
                @else
                    <p>Your Fire Name</p>
                @endif
                <p class="mb-3 text-sm font-semibold">
                    {{ $city }}
                    @if ($state)
                        , {{ $state }}
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <img
                    src="{{ gravatar(auth()->user()->email, 'campfire-profile') }}"
                    class="rounded-full"
                    alt=""
                />
                <div class="text-sm text-grey-200">
                    Hosted by {{ auth()->user()->full_name }}
                </div>
            </div>
        </div>
    </div>
</div>
