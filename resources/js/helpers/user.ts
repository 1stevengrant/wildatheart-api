import { config } from "./config";

export const imageGenerator = (
    image: string | undefined,
    params: {
        [key: string]: string | number;
    } = {},
    cdnUrl?: string
): string => {
    if (!image) {
        return "";
    }

    // Use provided CDN URL or fall back to Bunny CDN
    const apiUrl = cdnUrl || config("services.bunny.api_url");

    // If the image already starts with http/https, return it as-is with params
    if (image.startsWith("http://") || image.startsWith("https://")) {
        const url = new URL(image);
        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, String(value));
        });
        return url.toString();
    }

    const stringParams: Record<string, string> = Object.fromEntries(
        Object.entries(params).map(([key, value]) => [key, String(value)])
    );

    const queryString = new URLSearchParams(stringParams).toString();
    return queryString ? `${apiUrl}/${image}?${queryString}` : `${apiUrl}/${image}`;
};
