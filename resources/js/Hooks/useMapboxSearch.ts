import { useState, useCallback } from "react";

import axios from "axios";

import { MapboxPlace } from "@/types/MapboxPlace";

export const useMapboxSearch = () => {
    const [query, setQuery] = useState<string>("");
    const [results, setResults] = useState<MapboxPlace[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    const queryMapbox = useCallback(async (searchQuery: string) => {
        if (!searchQuery) {
            setResults([]);
            return [];
        }

        setIsLoading(true);
        setError(null);

        try {
            const response = await axios.get(
                `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(
                    searchQuery
                )}.json`,
                {
                    params: {
                        access_token: import.meta.env
                            .VITE_REACT_APP_MAPBOX_TOKEN,
                        limit: 5, // Limit the number of results
                    },
                }
            );

            if (response.data && response.data.features) {
                const mapboxPlaces: MapboxPlace[] = response.data.features.map(
                    (feature: any) => ({
                        id: feature.id,
                        place_name: feature.place_name,
                        center: feature.center,
                        // Add any other properties you need from the feature object
                    })
                );
                setResults(mapboxPlaces);
                return mapboxPlaces;
            } else {
                setResults([]);
                return [];
            }
        } catch (error) {
            console.error("Error querying Mapbox", error);
            setError("Failed to fetch results. Please try again.");
            setResults([]);
            return [];
        } finally {
            setIsLoading(false);
        }
    }, []);

    return {
        queryMapbox,
        query,
        setQuery,
        results,
        setResults,
        isLoading,
        error,
    };
};
