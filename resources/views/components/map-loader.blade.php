<div
    id="map-loader"
    {{ $attributes->class(['mt-4 flex items-center space-x-2 text-white']) }}
>
    <x-icons.spinner />
    <span class="animate-pulse">{{ __('Loading Map...') }}</span>
</div>
