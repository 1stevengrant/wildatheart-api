<div class="mt-[75px] md:mt-[107px] relative">
    <a href="{{ route('home') }}" class="bg-white text-black absolute top-2 left-2 z-50 rounded-full">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor" class="w-6 h-6 stroke-current">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 9l-3 3m0 0l3 3m-3-3h7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

    </a>
    <div id="map" class="h-[150px] md:h-[300px]"></div>
    <div id="campfire"
         class="p-6 rounded-t-2xl border-t border-grey-darker text-white">

        <div class="container mx-auto">
            <div class="md:flex md:space-x-8">
                <div class="md:w-2/3">
                    <h1 class="text-3xl leading-tighter mb-1">{{ $campfire->title() }}</h1>
                    <h2 class="text-xl leading-tighter mb-8">{{ $campfire->location() }}</h2>

                    <x-campfire-host-info
                        :campfire="$campfire"/>

                    <x-campfire-meta
                        :campfire="$campfire"/>

                    <div class="mt-8 space-y-4 pb-12 md:pb-0">

                        <p class="font-semibold">Guidelines for Fire participants:</p>

                        <div class="space-y-6 pt-6">
                            <div class="flex items-center space-x-8">
                                <x-icons.megaphone class="flex-shrink-0 "/>
                                <div>
                                    <span class="font-semibold">1. Fires aren’t religious</span> <br>
                                    Leave your religious and professional credentials behind.
                                </div>
                            </div>

                            <div class="flex items-center space-x-8">
                                <x-icons.book class="flex-shrink-0 "/>
                                <div>
                                    <span class="font-semibold">2. Your story matters</span> <br>
                                    Fires are not a place for teaching or a podium for experts.
                                </div>
                            </div>

                            <div class="flex items-center space-x-8">
                                <x-icons.poop class="flex-shrink-0 "/>
                                <div>
                                    <span class="font-semibold">3. No bullshit allowed</span> <br>
                                    Fires are a place for authentic masculinity. No posing.
                                </div>
                            </div>

                            <div class="flex items-center space-x-8">
                                <x-icons.shield class="flex-shrink-0 "/>
                                <div>
                                    <span class="font-semibold">4. Confidentiality is essential</span> <br>
                                    What is shared at a Fire stays at the Fire.
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="md:w-1/3 hidden md:block">
                    @if(!session()->has('connected'))
                        <x-campfire-cta
                            :campfire="$campfire"/>
                    @else
                        <div class="border border-grey-darker p-8 rounded-lg">
                            <x-button-container>
                                <x-primary-button
                                    disabled
                                    class="w-full">
                                    You have connected with {{ $campfire->host->given_name }}
                                </x-primary-button>
                                <p class="text-sm mt-2">Thank you! We've forwarded your details to {{
                                $campfire->host->given_name }} and
                                    he'll be in touch directly.</p>
                            </x-button-container>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden md:block mt-12">
                <h2 class="text-2xl">Photos of Fires Like This</h2>

                <div class="flex items-center space-x-4 mt-12">
                    <img src="{{ asset('img/fires/Image1.webp') }}" class="object-fill rounded-lg" alt="">
                    <div class="grid gap-4 grid-cols-2">
                        <img src="{{ asset('img/fires/Image2.webp') }}" class="rounded-lg" alt="">
                        <img src="{{ asset('img/fires/Image3.webp') }}" class="rounded-lg" alt="">
                        <img src="{{ asset('img/fires/Image.webp') }}" class="rounded-lg" alt="">
                        <img src="{{ asset('img/fires/Image4.webp') }}" class="rounded-lg" alt="">
                    </div>
                </div>
            </div>

            <x-campfire-carousel/>
        </div>
    </div>

    <div id="mobile-connect-with-host"
         class="md:hidden px-4 py-6 fixed bottom-0 left-0 right-0 border-t border-grey-darker bg-black">
        @if(!session()->has('connected'))
            <x-primary-link
                href="{{ route('connect.host', $campfire) }}"
                class="w-full">
                Connect with {{ $campfire->host->given_name }}
            </x-primary-link>
        @else
            <x-primary-button
                disabled
                class="w-full">
                You have connected with {{ $campfire->host->given_name }}
            </x-primary-button>
        @endif
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
        document.title = "{!!  $campfire->title() ?? $campfire->location() !!} | {{ config('app.name') }}";
        if (window.innerWidth < 768) {
            const campfireElement = document.getElementById('campfire'),
                connectWithHostElement = document.getElementById('mobile-connect-with-host');

            campfireElement.style.paddingBottom = (connectWithHostElement.offsetHeight + 16) + 'px';
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
        interactive: false,
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
