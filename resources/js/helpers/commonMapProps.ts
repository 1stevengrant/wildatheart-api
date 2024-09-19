import { config } from "@/helpers/config";

export const commonMapProps = {
    minZoom: 0,
    maxZoom: 13,
    maxPitch: 0,
    minPitch: 0,
    pitchWithRotate: false,
    dragRotate: false,
    touchPitch: false,
    customAttribution: ``,
    attributionControl: false,
    style: { width: "100%", height: "100%", borderRadius: "0.5rem" },
    mapboxAccessToken: config("services.mapbox.token"),
    mapStyle: `https://api.maptiler.com/maps/streets/style.json?key=sr5EVCtJBfz1IQnASSwn`,
};
