import { LngLat } from "./LngLat";

export type MapboxPlace = {
    address: string;
    center: LngLat;
    context: unknown[];
    geometry: {
        type: string;
        coordinates: LngLat;
    };
    id: string;
    place_name: string;
    place_type: string[];
    properties: unknown;
    relevance: number;
    text: string;
    type: string;
};
