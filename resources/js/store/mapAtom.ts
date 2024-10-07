import { atom } from "recoil";

const selectedPlaceState = atom<any | undefined>({
    key: "selectedPlace",
    default: undefined,
});

const zoomState = atom<number>({
    key: "zoom",
    default: 6,
});

export { selectedPlaceState, zoomState };
