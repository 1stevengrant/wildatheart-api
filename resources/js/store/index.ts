import { atom } from 'recoil';
import {GeoHit} from "@/types/FireHit";
import {LngLat} from "@/types/LngLat";

export const currentFireAtom = atom<GeoHit | null | undefined>({
    key: 'currentFireAtom',
    default: undefined
});

export const selectedPlaceAtom = atom<any | undefined>({
    key: 'selectedPlaceAtom',
    default: undefined
});

export const flyToLocationAtom = atom<LngLat | undefined>({
    key: 'flyToLocationAtom',
    default: undefined
});
