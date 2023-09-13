import {LngLat} from "./LngLat";

export type MapboxPlace = {
    address: string;
    center: LngLat;
    context: {}[];
    geometry: {
        type: string,
        coordinates: LngLat
    };
    id: string;
    place_name: string;
    place_type: string[];
    properties: {};
    relevance: number;
    text: string;
    type: string;
}
