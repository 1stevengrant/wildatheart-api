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

        let userMarker = new mapboxgl.Marker({
            element: createCustomMarkerElement(),
            draggable: false
        })

        function createCustomMarkerElement() {
            let el = document.createElement('div');
            el.style.backgroundImage = 'url({{ asset('svgs/map-campfire-marker.svg') }})';
            el.style.width = '50px';
            el.style.height = '50px';
            el.style.backgroundSize = 'cover';
            return el;
        }

        geocoder.addTo('#geocoder');
        const geocoderInput = document.querySelector('.mapboxgl-ctrl-geocoder--input');
        geocoderInput.setAttribute('autocomplete', 'off');

        // Get the geocoder results container.
        const results = document.getElementById('result');

        // Add geocoder result to container.
        geocoder.on('result', async (event) => {
            let coords = event.result.center;
            @this.set("longitude", coords[0]);
            @this.set("latitude", coords[1]);

            const address = await lookupAddress(coords[0] + ',' + coords[1]);

            if (!address) {
                const context = event.result.context;
                console.log('from geocoder result', event.result)
                @this.set('country', context.find(feature => feature.id.includes('country'))?.short_code);
                @this.set('region', context.find(feature => feature.id.includes('region'))?.text);
                @this.set('locality', context.find(feature => feature.id.includes('locality'))?.text);
                @this.set('street_address', context.find(feature => feature.id.includes('address'))?.text);
                @this.set('district', context.find(feature => feature.id.includes('district'))?.text);
                @this.set('neighborhood', context.find(feature => feature.id.includes('neighborhood'))?.text);
                @this.set('city', event.result.text ?? null);
                @this.set('state', context.find(feature => feature.id.includes('region'))?.text);
                @this.set('zip', context.find(feature => feature.id.includes('postcode'))?.text);
            }

            setGeoJson(coords[0], coords[1])
            map.getSource("point").setData(geojson);
            flyToLocation(coords[0], coords[1]);
            userMarker.setLngLat([coords[0], coords[1]]).addTo(map);
        });

        geocoder.on('clear', () => {
            results.innerText = '';
        });

        map.addControl(new mapboxgl.NavigationControl({
            showCompass: false
        }));

        map.dragRotate.disable();
        map.doubleClickZoom.disable();
        map.touchZoomRotate.disableRotation();
        map.touchPitch.disable();
        map.setMaxPitch(0);
        map.setMinPitch(0)

        function addMarker(event) {
            let coordinates = event.lngLat;
            userMarker.setLngLat([coordinates.lng, coordinates.lat]).addTo(map);
            @this.set("longitude", coordinates.lng);
            @this.set("latitude", coordinates.lat);

            lookupAddress(coordinates.lng + ',' + coordinates.lat);
            reverseGeocode(coordinates.lng + ',' + coordinates.lat);
        }

        async function reverseGeocode(latLng) {
            await fetch('{{config('services.mapbox.api_url')}}'+ latLng +'.json?access_token={{ config('services.mapbox.api_key') }}')
                .then(response => response.json())
                .then(data => {

                    const features = data.features;
                    if (features) {
                        console.log('from reverse geocode', features)
                        @this.set('mapboxError', '')
                        @this.set('city', features.find(feature => feature.id.includes('place'))?.text);
                        @this.set('street_address', features.find(feature => feature.id.includes('address'))?.text);
                        @this.set('state', features.find(feature => feature.id.includes('region'))?.text);
                        @this.set('country', features.find(feature => feature.id.includes('country'))?.properties.short_code);
                        @this.set('zip', features.find(feature => feature.id.includes('postcode'))?.text);
                        @this.set('district', features.find(feature => feature.id.includes('district'))?.text);
                        @this.set('neighborhood', features.find(feature => feature.id.includes('neighborhood'))?.text);
                        @this.set('locality', features.find(feature => feature.id.includes('locality'))?.text);
                        @this.set('region', features.find(feature => feature.id.includes('region'))?.text);
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        }

        map.on('click', addMarker);

        async function lookupAddress(latLng) {
            await fetch('{{ config('services.mapbox.api_url') }}' + latLng + '.json?types=address&access_token={{ config('services.mapbox.api_key') }}')
                .then(response => response.json())
                .then(data => {

                    const features = data.features.context;
                    if (features) {
                        console.log('from lookup address', features)
                        @this.set('city', features.find(feature => feature.id.includes('place'))?.text);
                        @this.set('street_address', features.find(feature => feature.id.includes('address'))?.text);
                        @this.set('state', features.find(feature => feature.id.includes('region'))?.text);
                        @this.set('country', features.find(feature => feature.id.includes('country'))?.properties.short_code);
                        @this.set('zip', features.find(feature => feature.id.includes('postcode'))?.text);
                        @this.set('district', features.find(feature => feature.id.includes('district'))?.text);
                        @this.set('neighborhood', features.find(feature => feature.id.includes('neighborhood'))?.text);
                        @this.set('locality', features.find(feature => feature.id.includes('locality'))?.text);
                        @this.set('region', features.find(feature => feature.id.includes('region'))?.text);
                        return
                    }

                    return false

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
