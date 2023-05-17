<div>
    <x-breadcrumbs :state="$fire->state"/>
    <x-type.h1>
        {{ prettyDate($fire->date) }} at {{ prettyTime($fire->time) }}
    </x-type.h1>

    <div>
        <div id="map"></div>
    </div>

    <div class="mt-6">
        {{ $fire->description }}
    </div>

    <div class="flex items-center space-x-4">

        @auth
            @if(!auth()->user()->isAttending($fire))
                <button
                    wire:click="$emit('modal.open', 'rsvp-for-fire', {'fire' : '{{ $fire->id }}'})"
                    class="bg-black text-white font-semibold p-4 inline-block">
                    RSVP
                </button>
            @endif

        @else
            <button wire:click="handleAttendee({{ $fire->id }})"
                    class="bg-blue-600
                                    text-white
                                    font-semibold p-4
                                    inline-block">
                RSVP
            </button>
        @endauth


    </div>

</div>
@push('head')
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <style>
        #map { height: 500px; width: 100%; }
        .marker {
            display: block;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            padding: 0;
        }
    </style>
@endpush
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = '{{ '🔥 on ' . prettyDate($fire->date)  }} | {{ config('app.name') }}';
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
                    'coordinates': [{{ $fire->coordinates }}]
                }
            }
        ]
    };

    const map = new mapboxgl.Map({
        center: [{{ $fire->coordinates }}],
        zoom: 10,
        maxZoom: 10,
        container: 'map',
        style: 'mapbox://styles/mapbox/dark-v11',
    });

    for (const marker of geojson.features) {
        const el = document.createElement('div');
        const width = marker.properties.iconSize[0];
        const height = marker.properties.iconSize[1];
        el.className = 'marker';
        el.style.backgroundImage = `url(https://placekitten.com/g/${width}/${height}/)`;
        el.style.width = `${width}px`;
        el.style.height = `${height}px`;
        el.style.backgroundSize = '100%';

        new mapboxgl.Marker(el)
            .setLngLat(marker.geometry.coordinates)
            .addTo(map);
    }
    </script>
@endpush
