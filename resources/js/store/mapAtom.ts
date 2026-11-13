import { atom } from "recoil";

import { MapboxPlace } from "@/types/MapboxPlace";

const selectedPlaceState = atom<MapboxPlace | null>({
    key: "selectedPlace",
    default: null,
});

const zoomState = atom<number>({
    key: "zoom",
    default: 6,
});

export { selectedPlaceState, zoomState };
