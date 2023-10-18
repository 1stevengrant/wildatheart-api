export default function config(key: string): string | undefined {
    const configs: { [key: string]: string | undefined } = {
        'services.bunny.api_url': import.meta.env.VITE_BUNNY_CDN_URL,
        'services.mapbox.api_url': 'https://api.mapbox.com/geocoding/v5/mapbox.places/',
        'services.mapbox.token': import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN,
        'services.mapbox.style': 'mapbox://styles/mapbox/navigation-day-v1'
    };

    return configs[key];
}
