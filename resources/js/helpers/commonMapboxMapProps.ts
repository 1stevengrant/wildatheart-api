export const commonMapProps = {
    minZoom: 0,
    maxZoom: 13,
    maxPitch: 0,
    minPitch: 0,
    pitchWithRotate: false,
    dragRotate: false,
    touchPitch: false,
    customAttribution: "",
    attributionControl: false,
    mapStyle: "mapbox://styles/mapbox/outdoors-v12",
    mapboxAccessToken: import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN,
    style: {
        width: "100%",
        height: "100%",
        borderRadius: "0.5rem",
    },
};
