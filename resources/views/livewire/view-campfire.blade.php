<div>
    <div id="map" class="h-[300px]"></div>
    <div id="campfire"
         class="p-6 rounded-t-2xl border-t border-grey-darker text-white">

        <div class="container mx-auto md:flex md:space-x-8">
            <div class="md:w-2/3">
                <h1 class="text-3xl">{{ $campfire->city }}, {{ getStateAbbreviation($campfire->state) }}</h1>
                <x-campfire-carousel/>

                <x-campfire-host-info :campfire="$campfire"/>

                <x-campfire-meta :campfire="$campfire"/>

            </div>

            <div class="md:w-1/3 hidden md:block">
                <x-campfire-cta :campfire="$campfire"/>
            </div>


        </div>

    </div>
</div>
@push('head')
    <style>
        #footer {
            display: none;
        }
    </style>
@endpush
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = '{{ '🔥 in ' . $campfire->city . ' ' . getStateAbbreviation($campfire->state)  }} | {{ config('app.name') }}';

        if (window.innerWidth < 768) {
            const screenHeight = window.innerHeight,
                mapBox = document.getElementById('map'),
                mapBoxHeight = mapBox.offsetHeight,
                footer = document.getElementById('footer'),
                footerHeight = footer.offsetHeight,
                campfiresContainer = document.getElementById('campfire');
            campfiresContainer.style.maxHeight = `${screenHeight - mapBoxHeight}px`;
            campfiresContainer.style.paddingBottom = `${footerHeight + 10}px`;
        }

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
