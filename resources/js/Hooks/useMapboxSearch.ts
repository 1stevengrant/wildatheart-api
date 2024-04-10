import { useState } from "react";

import axios from "axios";

import { MapboxPlace } from "@/types/MapboxPlace";

export const useMapboxSearch = () => {
    const [query, setQuery] = useState<string>("");
    const [results, setResults] = useState<MapboxPlace[]>([]);
    const queryMapbox = async (query: string) => {
        if (!query) return;

        try {
            const response = await axios.get(
                `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(
                    query
                )}.json`,
                {
                    params: {
                        access_token: import.meta.env
                            .VITE_REACT_APP_MAPBOX_TOKEN,
                    },
                }
            );

            if (response.data && response.data.features) {
                return response.data.features;
            }
        } catch (error) {
            console.error("Error querying Mapbox", error);
        }
    };

    return { queryMapbox, query, setQuery, results, setResults };
};
