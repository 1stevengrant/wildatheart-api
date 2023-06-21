<div class="mt-[75px] h-screen w-full text-white md:mt-[107px]">
    <div
        id="map"
        class="aspect-square h-full w-full md:border-grey-darker"
    ></div>
</div>
@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title = "Fires | {{ config('app.name') }}";
            if (window.innerWidth < 768) {
                const screenHeight = window.innerHeight,
                    mapBox = document.getElementById("map"),
                    mapBoxHeight = mapBox.offsetHeight,
                    footer = document.getElementById("footer"),
                    footerHeight = footer.offsetHeight;
            }
        });

        mapboxgl.accessToken = "{{ config('services.mapbox.api_key') }}";
        const map = new mapboxgl.Map({
            container: "map",
            style: "{{ config('services.mapbox.mapbox_style') }}",
            center: [{{ $longitude }}, {{ $latitude }}],
            zoom: 4,
            maxZoom: 10,
            attributionControl: false,
        });

        map.addControl(new mapboxgl.NavigationControl());

        let campfires = null;

        async function getCampFires() {
            const response = await fetch("/api/campfires");
            campfires = await response.json();
        }

        /**
         * Wait until the map loads to make changes to the map.
         */
        map.on("load", async () => {
            await getCampFires();

            /**
             * This is where your '.addLayer()' used to be, instead
             * add only the source without styling a layer
             */
            map.addSource("places", {
                type: "geojson",
                data: campfires,
            });

            map.resize();

            addMarkers();
        });

        function addMarkers() {
            for (const marker of campfires.features) {
                /* Create a div element for the marker. */
                const el = document.createElement("div");
                /* Assign a unique `id` to the marker. */
                el.id = `campfire-marker-${marker.properties.id}`;
                /* Assign the `marker` class to each marker for styling. */
                el.className = "campfire-marker";

                /**
                 * Create a marker using the div element
                 * defined above and add it to the map.
                 **/
                new mapboxgl.Marker(el, { offset: [0, -23] })
                    .setLngLat(marker.geometry.coordinates)
                    .addTo(map);

                /**
                 * Listen to the element and when it is clicked, do three things:
                 * 1. Fly to the point
                 * 2. Close all other popups and display popup for clicked fire
                 * 3. Highlight listing in sidebar (and remove highlight for all other listings)
                 **/
                el.addEventListener("click", (e) => {
                    /* Fly to the point */
                    flyToCampfire(marker);
                    /* Close all other popups and display popup for clicked fire */
                    createPopUp(marker);
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
                zoom: 15,
            });
        }

        /**
         * Create a Mapbox GL JS `Popup`.
         **/
        function createPopUp(currentFeature) {
            const popUps = document.getElementsByClassName("mapboxgl-popup");
            if (popUps[0]) popUps[0].remove();
            new mapboxgl.Popup({ closeOnClick: false })
                .setLngLat(currentFeature.geometry.coordinates)
                .setHTML(
                    `<div class='rounded-md shadow-lg p-2 md:p-4'>
    <a href='${
        currentFeature.properties.url
    }' class='flex items-stretch space-x-4'>

          <img src='${currentFeature.properties.image}'
             class='rounded-md flex-grow-0'
             alt='' />

        <div class='flex flex-col justify-between relative'>

            <div>
                <p class='text-white'>${
                    currentFeature.properties.title ?? ""
                }</p>

                <p class='text-white text-sm'>${
                    currentFeature.properties.location
                }</p>
            </div>
            <div class='flex items-center space-x-2'>
                <div class='avatar'
                     style='background-image: url(${currentFeature.properties.hostProfilePhoto}) !important;'></div>

                    <div class='text-[#BABABA] text-xs leading-tighter'>Hosted
                        by ${currentFeature.properties.host}</div>
            </div>
        </div>
    </a>
</div>`
                )
                .addTo(map);
        }
    </script>
@endpush
