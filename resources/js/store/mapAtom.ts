import { atom } from "recoil";

const selectedPlaceState = atom<any | undefined>({
    key: "SelectedPlaceState",
    default: undefined,
});

const zoomState = atom<number>({
    key: "ZoomState",
    default: 6,
});

export { selectedPlaceState, zoomState };
