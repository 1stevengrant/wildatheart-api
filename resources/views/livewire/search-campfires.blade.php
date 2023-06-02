<div class="md:w-[350px]">
    <div class="flex items-center bg-white rounded-full py-1 pl-3 pr-2 z-60 relative">
        <label for="search" class="w-full">
            <input type="search"
                   class="w-full border-white placeholder:text-xs md:placeholder:text-base outline-none focus:ring-0
                   ring-transparent focus:border-transparent "
                   id="search"
                   wire:model.debounce="searchQuery"
                   placeholder="Search Fires (Location or Host)"
                   name="search">
        </label>
        <button class="bg-orange flex-col items-center justify-center rounded-full p-4 w-2 h-2">
            <x-icons.search/>
        </button>
    </div>

    @if(!empty($searchQuery))
        <div class="absolute bg-white pt-2 pb-4 px-4 overflow-auto z-50 rounded-xl ml-[1px]">
            @if($campfires->isNotEmpty())
                @foreach ($campfires as $campfire)
                    <a class="block px-4 py-2 hover:border-orange border border-white rounded-xl"
                       href="{{$campfire->url }}">
                        {{ $campfire->title() }}
                    </a>
                @endforeach
            @else
                <div>
                    <p>{{ __('No Matching Fires ') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
