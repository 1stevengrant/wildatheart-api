<div class="w-full text-white h-screen">
    <div id="map"
         class="aspect-square h-full w-full md:border-grey-darker"></div>
</div>
@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'Fires | {{ config('app.name') }}';
        if (window.innerWidth < 768) {
            const screenHeight = window.innerHeight,
                mapBox = document.getElementById('map'),
                mapBoxHeight = mapBox.offsetHeight,
                footer = document.getElementById('footer'),
                footerHeight = footer.offsetHeight,
                campfiresContainer = document.getElementById('campfires-container');

            campfiresContainer.style.maxHeight = `${screenHeight - mapBoxHeight}px`;
            campfiresContainer.style.paddingBottom = `${footerHeight + 10}px`;
        }
    });

    mapboxgl.accessToken = '{{ config('services.mapbox.api_key') }}';
    const map = new mapboxgl.Map({
        container: 'map',
        style: '{{ config('services.mapbox.mapbox_style') }}',
        center: [{{ $longitude }}, {{ $latitude }}],
        zoom: 4,
        maxZoom: 10,
        // scrollZoom: false,
        attributionControl: false
    });

    map.addControl(new mapboxgl.NavigationControl());

    let campfires = null;

    async function getCampFires() {
        const response = await fetch('/api/campfires');
        campfires = await response.json();
    }

    /**
     * Wait until the map loads to make changes to the map.
     */
    map.on('load', async () => {

        await getCampFires();

        /**
         * This is where your '.addLayer()' used to be, instead
         * add only the source without styling a layer
         */
        map.addSource('places', {
            'type': 'geojson',
            'data': campfires
        });

        map.resize()

        addMarkers();
    })

    function addMarkers() {

        const homeMarker = document.createElement('div');
        homeMarker.className = 'home-marker';
        new mapboxgl.Marker(homeMarker)
            .setLngLat([{{ $longitude }}, {{ $latitude }}])
            .addTo(map);

        for (const marker of campfires.features) {
            /* Create a div element for the marker. */
            const el = document.createElement('div');
            /* Assign a unique `id` to the marker. */
            el.id = `campfire-marker-${marker.properties.id}`;
            /* Assign the `marker` class to each marker for styling. */
            el.className = 'campfire-marker';

            /**
             * Create a marker using the div element
             * defined above and add it to the map.
             **/
            new mapboxgl.Marker(el, {offset: [0, -23]})
                .setLngLat(marker.geometry.coordinates)
                .addTo(map);

            /**
             * Listen to the element and when it is clicked, do three things:
             * 1. Fly to the point
             * 2. Close all other popups and display popup for clicked fire
             * 3. Highlight listing in sidebar (and remove highlight for all other listings)
             **/
            el.addEventListener('click', (e) => {
                /* Fly to the point */
                flyToCampfire(marker);
                /* Close all other popups and display popup for clicked fire */
                createPopUp(marker);
                /* Highlight listing in sidebar */
                const activeItem = document.getElementsByClassName('active');
                e.stopPropagation();
                if (activeItem[0]) {
                    activeItem[0].classList.remove('active');
                }
                const listing = document.getElementById(
                    `campfire-${marker.properties.id}`
                );
                listing.classList.add('active');
            });
        }
    }

    /**
     * Use Mapbox GL JS's `flyTo` to move the camera smoothly
     * a given center point.
     **/
    function flyToCampfire(currentFeature) {
        map.flyTo({
            duration: 1500,
            center: currentFeature.geometry.coordinates,
            zoom: 15
        });
    }

    /**
     * Create a Mapbox GL JS `Popup`.
     **/
    function createPopUp(currentFeature) {
        const popUps = document.getElementsByClassName('mapboxgl-popup');
        if (popUps[0]) popUps[0].remove();
        new mapboxgl.Popup({closeOnClick: false})
            .setLngLat(currentFeature.geometry.coordinates)
            .setHTML(
                `<h3>${currentFeature.properties.city}, ${currentFeature.properties.state}</h3><h4>Host:
                ${currentFeature.properties.host}</h4><a href="${currentFeature.properties.url}">View Fire</a>`
            )
            .addTo(map);
    }

    </script>

@endpush
