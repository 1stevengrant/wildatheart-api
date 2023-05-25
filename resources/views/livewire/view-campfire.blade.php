<div>
    <div id="map" class="h-[300px]"></div>
    <div class="p-6 rounded-t-2xl border-t border-grey-darker text-white">
        <h1 class="text-3xl">{{ $campfire->city }}, {{ getStateAbbreviation($campfire->state) }}</h1>
        <x-campfire-carousel/>
        <x-campfire-host-info :campfire="$campfire"/>

        <div class="border-t border-b border-grey-darker py-8 mt-[25px] space-y-4">
            <div class="flex items-start">
                <x-icons.home/>
                <div class="ml-[24px] leading-[24px]">
                    <p class="font-medium">Hosted at a Home</p>
                    <p class="text-sm text-[#6B7280]">John has graciously offered up his
                        home to host this event.</p>
                </div>
            </div>
            <div class="flex items-start">
                <x-icons.event-type/>
                <div class="ml-[24px] leading-[24px]">
                    <p class="font-medium">Outdoor event</p>
                    <p class="text-sm text-[#6B7280]">Ths event is slated to be an outdoor event. Please dress
                        accordingly.</p>
                </div>
            </div>
            <div class="flex items-start">
                <x-icons.calendar/>
                <div class="ml-[24px] leading-[24px]">
                    <p class="font-medium">Scheduled</p>
                    <p class="text-sm text-[#6B7280]">
                        {{ $campfire->frequency }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = '{{ '🔥 in ' . $campfire->city . ' ' . getStateAbbreviation($campfire->state)  }} | {{ config('app.name') }}';
    });

    mapboxgl.accessToken = '{{ config('services.mapbox.api_key') }}';
    const geojson = {
        'type': 'FeatureCollection',
        'features': [
            {
                'type': 'Feature',
                'properties': {
                    'iconSize': [40, 40]
                },
                'geometry': {
                    'type': 'Point',
                    'coordinates': [{{ $campfire->coordinates }}]
                }
            }
        ]
    };

    const map = new mapboxgl.Map({
        center: [{{ $campfire->coordinates }}],
        zoom: 10,
        maxZoom: 10,
        container: 'map',
        attributionControl: false,
        style: '{{ config('services.mapbox.mapbox_style') }}'
    });

    for (const marker of geojson.features) {
        const el = document.createElement('div');
        const width = marker.properties.iconSize[0];
        const height = marker.properties.iconSize[1];
        el.className = 'marker';
        el.style.backgroundImage = `url(/svgs/map-campfire-marker.svg)`;
        el.style.width = `${width}px`;
        el.style.height = `${height}px`;
        el.style.backgroundSize = '100%';

        new mapboxgl.Marker(el)
            .setLngLat(marker.geometry.coordinates)
            .addTo(map);
    }
    </script>
@endpush
