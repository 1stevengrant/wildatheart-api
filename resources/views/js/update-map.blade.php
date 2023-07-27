<script>
    setTimeout(() => {
        mapboxgl.accessToken = "{{ config('services.mapbox.api_key') }}";
        const map = new mapboxgl.Map({
            container: "map",
            style: "{{ config('services.mapbox.mapbox_style') }}",
            center: [{{ $longitude }}, {{ $latitude }}],
            zoom: {{ config('services.mapbox.initial_zoom_level') }},
            attributionControl: false,
        });

        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }));
        map.dragRotate.disable();

        // disable map rotation using touch rotation gesture
        map.touchZoomRotate.disableRotation();

        const canvas = map.getCanvasContainer();

        const geojson = {
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

        function onMove(e) {
            const coords = e.lngLat;

            // Set a UI indicator for dragging.
            canvas.style.cursor = "grabbing";

            // Update the Point feature in `geojson` coordinates
            // and call setData to the source layer `point` on it.
            geojson.features[0].geometry.coordinates = [coords.lng, coords.lat];
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
                    console.log(data);
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

    }, 1000)
</script>
