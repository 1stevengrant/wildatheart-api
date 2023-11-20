import { atom } from 'recoil';
import {GeoHit} from "@/types/EventHit";
import {LngLat} from "@/types/LngLat";

export const currentEventAtom = atom<GeoHit | null | undefined>({
    key: 'currentEventAtom',
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

export const reactNotificationAtom = atom<Array<any>>({
    key: 'reactNotificationAtom',
    default: []
});
