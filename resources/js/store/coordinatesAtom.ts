import { atom } from "recoil";

export const latitudeState = atom({
    key: "Latitude",
    default: <number | null>null,
});

export const longitudeState = atom({
    key: "Longitude",
    default: <number | null>null,
});
