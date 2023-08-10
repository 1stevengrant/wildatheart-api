<div class="relative mt-[75px] md:mt-[107px]">
    <x-buttons.back-to-fires />
    <div id="map" class="h-[150px] md:h-[300px]"></div>
    <div
        id="campfire"
        class="rounded-t-2xl border-t border-grey-darker p-6 text-white"
    >
        <div class="container mx-auto">
            <div class="md:flex md:space-x-8">
                <div class="md:w-2/3">
                    <div class="flex items-center space-x-4">
                        <h1 class="mb-1 text-3xl leading-tighter">
                            {{ $campfire->name }} Fire
                        </h1>
                        @if ($campfire->isHost())
                            <a
                                href="{{ route('edit.campfire', $campfire) }}"
                                class="rounded-md bg-orange px-2 py-1 text-sm hover:bg-orange/80"
                            >
                                Edit Fire
                            </a>
                        @endif
                    </div>

                    <h2 class="mb-8 text-xl leading-tighter">
                        {{ $campfire->location }}
                    </h2>

                    <x-campfire-host-info :campfire="$campfire" />

                    <x-campfire-meta :campfire="$campfire" />

                    <x-campfire.guidelines />

                    <x-campfire.shipping-address-update
                        :campfire="$campfire"
                        class="md:hidden"
                    />
                </div>

                <div class="hidden md:block md:w-1/3">
                    @include('share')
                    @if (! session()->has('connected'))
                        <x-campfire-cta :campfire="$campfire" />
                    @else
                        <div class="rounded-lg border border-grey-darker p-8">
                            <x-button-container>
                                <x-primary-button disabled class="w-full">
                                    You have connected with
                                    {{ $campfire->host->given_name }}
                                </x-primary-button>
                                <p class="mt-2 text-sm">
                                    Thank you! We've forwarded your details to
                                    {{ $campfire->host->given_name }} and he'll
                                    be in touch directly.
                                </p>
                            </x-button-container>
                        </div>
                    @endif
                    <x-campfire.shipping-address-update
                        class="hidden rounded-lg border border-grey-darker p-8 md:block"
                        :campfire="$campfire"
                    />
                </div>
            </div>

            <div class="mt-12 hidden md:block">
                <h2 class="text-2xl">Photos of Fires Like This</h2>

                <div class="mt-12 flex items-center space-x-4">
                    <img
                        src="{{ asset('img/fires/Image1.webp') }}"
                        class="rounded-lg object-fill"
                        alt=""
                    />
                    <div class="grid grid-cols-2 gap-4">
                        <img
                            src="{{ asset('img/fires/Image2.webp') }}"
                            class="rounded-lg"
                            alt=""
                        />
                        <img
                            src="{{ asset('img/fires/Image3.webp') }}"
                            class="rounded-lg"
                            alt=""
                        />
                        <img
                            src="{{ asset('img/fires/Image.webp') }}"
                            class="rounded-lg"
                            alt=""
                        />
                        <img
                            src="{{ asset('img/fires/Image4.webp') }}"
                            class="rounded-lg"
                            alt=""
                        />
                    </div>
                </div>
            </div>

            <x-campfire-carousel />
        </div>
    </div>

    <div
        id="mobile-connect-with-host"
        class="fixed bottom-0 left-0 right-0 border-t border-grey-darker bg-black px-4 py-6 md:hidden"
    >
        <div class="flex items-center space-x-6">
            <x-share-button id="share-btn" />
            @if (! session()->has('connected'))
                <x-primary-link
                    href="{{ route('connect.host', $campfire) }}"
                    class="w-full"
                >
                    Connect with {{ $campfire->host->given_name }}
                </x-primary-link>
            @else
                <x-primary-button disabled class="w-full">
                    You have connected with {{ $campfire->host->given_name }}
                </x-primary-button>
            @endif
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
        document.addEventListener("livewire:load", function () {
            if (window.innerWidth < 768) {
                const campfireElement = document.getElementById("campfire"),
                    connectWithHostElement = document.getElementById(
                        "mobile-connect-with-host"
                    );

                campfireElement.style.paddingBottom =
                    connectWithHostElement.offsetHeight + 16 + "px";
            }
        });

        mapboxgl.accessToken = "{{ config('services.mapbox.api_key') }}";
        const geojson = {
            type: "FeatureCollection",
            features: [
                {
                    type: "Feature",
                    properties: {
                        iconSize: [40, 40],
                    },
                    geometry: {
                        type: "Point",
                        coordinates: [{{ $campfire->coordinates }}],
                    },
                },
            ],
        };

        const map = new mapboxgl.Map({
            center: [{{ $campfire->coordinates }}],
            zoom: {{ config('services.mapbox.initial_zoom_level') }},
            maxZoom: 10,
            container: "map",
            attributionControl: false,
            interactive: false,
            style: "{{ config('services.mapbox.mapbox_style') }}",
        });

        for (const marker of geojson.features) {
            const el = document.createElement("div");
            const width = marker.properties.iconSize[0];
            const height = marker.properties.iconSize[1];
            el.className = "marker";
            el.style.backgroundImage = `url(/svgs/map-campfire-marker.svg)`;
            el.style.width = `${width}px`;
            el.style.height = `${height}px`;
            el.style.backgroundSize = "100%";

            new mapboxgl.Marker(el)
                .setLngLat(marker.geometry.coordinates)
                .addTo(map);
        }

        const shareBtn = document.querySelector("#share-btn");
        shareBtn.addEventListener("click", async () => {
            try {
                await navigator.share({
                    title: "{{ $campfire->name }}",
                    text: "{{ $campfire->location }}",
                    url: "{{ route('view.campfire', $campfire) }}",
                });
            } catch (err) {
                console.error(err);
            }
        });

        function copyToClipboard(url) {
            document
                .getElementById("share-component")
                .dispatchEvent(new CustomEvent("toggle-share", { detail: {} }));
            Livewire.emit("copiedToClipboard");
            const el = document.createElement("textarea");
            el.value = url;
            document.body.appendChild(el);
            el.select();
            document.execCommand("copy");
            document.body.removeChild(el);
        }
    </script>
@endpush
