@props([
    'mapboxError',
    'type',
])

<div wire:ignore>
    <h2 class="mb-3 block text-sm font-semibold text-grey-200">
        {{ $type ?? 'Update' }} Fire Location
        <span class="text-red">*</span>
    </h2>
    <x-forms.instruction
        instruction="Touch the map to drop a pin on the location of your Fire. You can also search for an address in
        the search box below."
    />

    <div id="geocoder" class="my-4"></div>
    <pre id="results"></pre>

    <div
        id="map"
        class="absolute bottom-0 left-0 right-0 top-0 mt-2 h-[600px] max-h-[400px] sm:max-h-[500px] md:max-h-[600px]"
    ></div>
    <x-map-loader />
</div>
