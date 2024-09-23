import { config } from "@/helpers/config";

export const commonMapProps = {
    dragRotate: false,
    touchPitch: false,
    pitchWithRotate: false,
    maxPitch: 0,
    minPitch: 0,
    style: {
        width: "100%",
        height: "100%",
        borderRadius: "0.5rem",
    },
    mapboxAccessToken: config("services.mapbox.token"),
    customAttribution: ``,
    attributionControl: false,
    mapStyle: `https://api.maptiler.com/maps/streets/style.json?key=1kAGKS8dAMyREqM1ZFrT`,
};
