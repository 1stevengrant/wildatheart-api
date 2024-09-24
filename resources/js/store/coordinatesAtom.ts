import { atom } from "recoil";

import { defaultLatLng } from "@/helpers/defaultLatLng";

export const latitudeState = atom({
    key: "LatitudeState",
    default: defaultLatLng.latitude,
});

export const longitudeState = atom({
    key: "LongitudeState",
    default: defaultLatLng.longitude,
});

export const boundsState = atom({
    key: "BoundsState",
    default: {
        _sw: { lng: -108.91317331111544, lat: 37.056855986991906 },
        _ne: { lng: -102.24933247057001, lat: 41.72490306133824 },
    },
});
