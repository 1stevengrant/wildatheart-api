<div class="rounded-md border-grey-darker border p-5 w-1/2 shadow-lg">
    <div class="flex items-stretch space-x-4">
        <img src="{{ asset('img/campfire.png') }}"
             class="rounded-md"
             alt="">
        <div class="flex flex-col justify-between relative">
            <div>
                <p class="text-xs text-grey-200 capitalize">{{ $frequency }}</p>
                @if($campfireName)
                    <p>{{ $campfireName }} Fire</p>
                @else
                    <p>Your Fire Name</p>
                @endif
                <p class="text-sm font-semibold mb-3">
                    {{ $city }} @if($state), {{ $state }}@endif
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <img src="{{ gravatar(auth()->user()->email, 'campfire-profile') }}"
                     class="rounded-full"
                     alt="">
                <div class="text-[#BABABA] text-sm">Hosted by {{ auth()->user()->full_name }}</div>
            </div>
        </div>
    </div>
</div>
