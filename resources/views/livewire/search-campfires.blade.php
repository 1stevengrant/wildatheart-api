<div class="md:w-[350px]">
    <div
        class="z-60 relative flex items-center rounded-full bg-white py-1 pl-3 pr-2"
    >
        <label for="search" class="w-full">
            <input
                type="search"
                class="w-full border-white outline-none ring-transparent placeholder:text-xs focus:border-transparent focus:ring-0 md:placeholder:text-base"
                id="search"
                wire:model.debounce="searchQuery"
                placeholder="Search Fires (Location or Host)"
                name="search"
            />
        </label>
        <button
            class="h-2 w-2 flex-col items-center justify-center rounded-full bg-orange p-4"
        >
            <x-icons.search />
        </button>
    </div>

    @if (! empty($searchQuery))
        <div
            class="absolute z-50 ml-[1px] overflow-auto rounded-xl bg-white px-4 pb-4 pt-2"
        >
            @if ($campfires->isNotEmpty())
                @foreach ($campfires as $campfire)
                    <a
                        class="block rounded-xl border border-white px-4 py-2 hover:border-orange"
                        href="{{ $campfire->url }}"
                    >
                        {{ $campfire->title }}
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
