<script>
    setTimeout(() => {
        let geojson = {
            type: "FeatureCollection",
            features: [
                {
                    type: "Feature",
                    geometry: {
                        type: "Point",
                        coordinates: [{{ $longitude }}, {{ $latitude }}]
                    }
                }
            ]
        };

        mapboxgl.accessToken = "{{ config('services.mapbox.api_key') }}";
        const map = new mapboxgl.Map({
            container: "map",
            style: "{{ config('services.mapbox.mapbox_style') }}",
            center: [{{ $longitude }}, {{ $latitude }}],
            zoom: {{ config('services.mapbox.initial_zoom_level') }},
            attributionControl: false,
        });

        const geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            placeholder: 'Lookup City Name',
            types: 'place, locality',
        });

        geocoder.addTo('#geocoder');

        // Get the geocoder results container.
        const results = document.getElementById('result');

        // Add geocoder result to container.
        geocoder.on('result', (e) => {
            let coords = e.result.center;
            @this.set("longitude", coords[0]);
            @this.set("latitude", coords[1]);
            lookupAddress(coords[0] + ',' + coords[1]);
            setGeoJson(coords[0], coords[1])
            map.getSource("point").setData(geojson);
            flyToLocation(coords[0], coords[1])
        });

        // Clear results container when search is cleared.
        geocoder.on('clear', () => {
            results.innerText = '';
        });

        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }));
        map.dragRotate.disable();

        // disable map rotation using touch rotation gesture
        map.touchZoomRotate.disableRotation();

        const canvas = map.getCanvasContainer();

        function onMove(e) {
            const coords = e.lngLat;

            // Set a UI indicator for dragging.
            canvas.style.cursor = "grabbing";

            // Update the Point feature in `geojson` coordinates
            // and call setData to the source layer `point` on it.
            setGeoJson(coords.lng, coords.lat)
            map.getSource("point").setData(geojson);
        }

        function onUp(e) {
            const coords = e.lngLat;
            @this.set("longitude", coords.lng);
            @this.set("latitude", coords.lat);
            canvas.style.cursor = "";
            lookupAddress(coords.lng + ',' + coords.lat);

            // Unbind mouse/touch events
            map.off("mousemove", onMove);
            map.off("touchmove", onMove);
        }

        async function lookupAddress(latLng) {
            await fetch('{{ config('services.mapbox.api_url') }}' + latLng + '.json?types=address&access_token={{ config('services.mapbox.api_key') }}')
                .then(response => response.json())
                .then(data => {
                    const features = data.features[0];
                    if (features) {
                        @this.set('mapboxError', '')
                        const placeName = features && features.hasOwnProperty('place_name') ? features.place_name : null,
                            streetAddress = placeName ? placeName.split(",").map(component => component.trim())[0] : null,
                            context = features.context,
                            city = context.find(feature => feature.id.includes('place'))?.text,
                            state = context.find(feature => feature.id.includes('region'))?.short_code
                                .replace("US-", "").toUpperCase(),
                            country = context.find(feature => feature.id.includes('country'))?.short_code,
                            zip = context.find(feature => feature.id.includes('postcode'))?.text;

                        @this.set('street_address', streetAddress);
                        @this.set('city', city);
                        @this.set('state', state);
                        @this.set('country', country);
                        @this.set('zip', zip);
                    } else {
                        @this.set('mapboxError', 'Unable to find location. Please try again.');
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        }

        function setGeoJson(longitude, latitude) {
            geojson.features[0].geometry.coordinates = [longitude, latitude];
        }

        map.on("load", () => {
            // Add a single point to the map.
            map.addSource("point", {
                type: "geojson",
                data: geojson
            });

            map.addLayer({
                id: "point",
                type: "circle",
                source: "point",
                paint: {
                    "circle-radius": 20,
                    "circle-color": "#F84C4C" // red color
                }
            });

            // When the cursor enters a feature in
            // the point layer, prepare for dragging.
            map.on("mouseenter", "point", () => {
                map.setPaintProperty("point", "circle-color", "#3bb2d0");
                canvas.style.cursor = "move";
            });

            map.on("mouseleave", "point", () => {
                map.setPaintProperty("point", "circle-color", "#3887be");
                canvas.style.cursor = "";
            });

            map.on("mousedown", "point", (e) => {
                // Prevent the default map drag behavior.
                e.preventDefault();

                canvas.style.cursor = "grab";

                map.on("mousemove", onMove);
                map.once("mouseup", onUp);
            });

            map.on("touchstart", "point", (e) => {
                if (e.points.length !== 1) return;

                // Prevent the default map drag behavior.
                e.preventDefault();

                map.on("touchmove", onMove);
                map.once("touchend", onUp);
            });
        });

        document.querySelector('#map-loader').classList.add('hidden');

        function flyToLocation(longitude, latitude, duration = 1000) {
            map.flyTo({
                center: [longitude, latitude],
                essential: true,
                duration: duration
            });
        }

    }, 1000)
</script>
