import { config } from "@/helpers/config";

export type GeocodedFeature = {
    id: string;
    text: string;
    context?: Array<{ id: string; text: string }>;
};

/**
 * Resolves with the features at a point, or null when the lookup fails or
 * is aborted.
 */
export const lookupLocation = async (
    longitude: number,
    latitude: number,
    signal?: AbortSignal
): Promise<GeocodedFeature[] | null> => {
    try {
        const response = await fetch(
            `https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${config("services.mapbox.token")}`,
            { signal }
        );

        if (!response.ok) {
            return null;
        }

        const data = await response.json();

        return data.features ?? null;
    } catch {
        return null;
    }
};
