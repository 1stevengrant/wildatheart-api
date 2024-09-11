import { atom } from "recoil";

import { defaultLatLng } from "@/helpers/defaultLatLng";

export const latitudeState = atom({
    key: "Latitude",
    default: defaultLatLng.latitude,
});

export const longitudeState = atom({
    key: "Longitude",
    default: defaultLatLng.longitude,
});

export const boundsState = atom({
    key: "Bounds",
    default: {
        swLatitude: defaultLatLng.latitude,
        swLongitude: defaultLatLng.longitude,
        neLatitude: defaultLatLng.latitude,
        neLongitude: defaultLatLng.longitude,
    },
});
