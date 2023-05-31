<div x-data="{connectToHost: false}"
     @registered-for-campfire.window="connectToHost = false">
    <div id="map" class="h-[300px]"></div>
    <div id="campfire"
         class="p-6 rounded-t-2xl border-t border-grey-darker text-white">

        <div class="container mx-auto">
            <div class="md:flex md:space-x-8">
                <div class="md:w-2/3">
                    <h1 class="text-3xl leading-tighter mb-8">{{ $campfire->title() }}</h1>

                    <x-campfire-carousel/>

                    <x-campfire-host-info :campfire="$campfire"/>

                    <x-campfire-meta :campfire="$campfire"/>

                </div>

                <div class="md:w-1/3 hidden md:block">
                    <x-campfire-cta :campfire="$campfire"/>
                </div>
            </div>

            <div class="mt-12">
                <h2 class="text-2xl">Photos of Fires Like This</h2>

                <div class="grid grid-cols-2 gap-2 mt-8">
                    <div>image 1</div>
                    <div class="grid gap-2 grid-cols-2">
                        <div>image 2</div>
                        <div>image 3</div>
                        <div>image 4</div>
                        <div>image 5</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mobile-connect-with-host"
         x-show="!connectToHost"
         class="md:hidden px-4 py-6 fixed bottom-0 left-0 right-0 border-t border-grey-darker bg-black">
        <x-primary-button
            @click="connectToHost = true"
            class="w-full">
            Connect with Host
        </x-primary-button>
    </div>


    <div x-show="connectToHost"
         x-transition>
        <livewire:connect-to-host
            :campfire="$campfire"
            :wire:key="$campfire->id"/>
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
        document.title = '{{ $campfire->title() }} | {{ config('app.name') }}';

        if (window.innerWidth < 768) {
            const campfireElement = document.getElementById('campfire'),
                connectWithHostElement = document.getElementById('mobile-connect-with-host');

            campfireElement.style.paddingBottom = connectWithHostElement.offsetHeight + 'px';
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
