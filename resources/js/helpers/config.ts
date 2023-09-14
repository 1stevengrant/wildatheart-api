export default function config(key: string): string | undefined {
    const configs: { [key: string]: string | undefined } = {
        'services.bunny.api_url': import.meta.env.VITE_BUNNY_CDN_URL
    };

    return configs[key];
}
