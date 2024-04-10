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
