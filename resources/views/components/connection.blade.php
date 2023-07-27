@props([
    'connection',
])
<li
    {{ $attributes->class(['col-span-1 divide-y divide-grey-darker rounded-lg border border-grey-darker shadow-lg']) }}
>
    <div class="flex w-full items-center justify-between space-x-6 p-6">
        <div class="flex-1 truncate">
            <div class="flex items-center space-x-3">
                <h3 class="truncate text-sm font-medium text-grey-200">
                    {{ $connection->full_name }},
                    {{ $connection->city }}@if ($connection->state), {{ $connection->state }}
                    @endif
                </h3>
            </div>
            <p class="mt-1 truncate text-sm text-grey-200">
                {{ $connection->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
    <div>
        <div
            class="-mt-px flex flex-col divide-x divide-y divide-grey-darker md:flex-row md:divide-y-0"
        >
            <div class="flex flex-1 md:w-0">
                <a
                    href="mailto:{{ $connection->email }}"
                    class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent px-3 py-4 text-sm font-semibold text-grey-200 transition-all duration-150 ease-in-out hover:text-orange"
                >
                    <x-icons.email />
                    <span class="overflow-hidden text-ellipsis">
                        {{ $connection->email }}
                    </span>
                </a>
            </div>
            @if ($connection->phone)
                <div class="-ml-px flex flex-1 md:w-0">
                    <a
                        href="tel:{{ $connection->contactNumber }}"
                        class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-grey-200 transition-all duration-150 ease-in-out hover:text-orange"
                    >
                        <x-icons.phone />
                        {{ $connection->contactNumber }}
                    </a>
                </div>
            @endif

            <div class="-ml-px flex flex-1 md:w-0">
                <button
                    wire:click="toggleContactedStatus('{{ $connection->id }}')"
                    class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-grey-200 transition-all duration-150 ease-in-out hover:text-orange"
                >
                    @if ($connection->hasBeenContacted())
                        <x-icons.check-circle />
                        <span>{{ __('Contacted') }}</span>
                    @else
                        <x-icons.empty-circle />
                        <span>{{ __('Not Contacted') }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>
</li>
