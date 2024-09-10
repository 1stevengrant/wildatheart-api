import { atom } from "recoil";

import { defaultLatLng } from "@/helpers/defaultLatLng";
import { LngLat } from "@/types/LngLat";

const currentLatState = atom<number>({
    key: "currentLat",
    default: defaultLatLng.latitude,
});

const currentLngState = atom<number>({
    key: "currentLng",
    default: defaultLatLng.longitude,
});

const selectedPlaceState = atom<any | undefined>({
    key: "selectedPlace",
    default: undefined,
});

const flyToLocationState = atom<LngLat | undefined>({
    key: "flyToLocation",
    default: undefined,
});

const zoomState = atom<number>({
    key: "zoom",
    default: 6,
});

export {
    currentLatState,
    currentLngState,
    selectedPlaceState,
    flyToLocationState,
    zoomState,
};
