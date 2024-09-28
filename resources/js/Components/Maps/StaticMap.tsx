import { useEffect } from "react";

import mapboxgl from "mapbox-gl";

import { config } from "@/helpers/config";
import { IStaticMapProps } from "@/interfaces/IStaticMapProps";

export const StaticMap = ({
    lat,
    lng,
    event,
    className = "",
}: IStaticMapProps) => {
    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN;
        let mapMarkerUrl = "";
        if (event?.eventType.includes("retreat")) {
            mapMarkerUrl = "/svgs/map-marker-retreat.svg";
        } else if (event?.eventType.includes("experience")) {
            mapMarkerUrl = "/svgs/map-marker-experience.svg";
        } else {
            mapMarkerUrl = "/svgs/map-marker-fire.svg";
        }
        const el = document.createElement("div");
        el.className = "custom-marker";
        el.style.backgroundImage = `url(${mapMarkerUrl})`;
        el.style.width = "50px";
        el.style.height = "50px";

        const map = new mapboxgl.Map({
            container: "mapContainer",
            style: config("services.mapbox.style"),
            center: [lng, lat], // coordinates of the marker (lon, lat)
            zoom: 9,
            attributionControl: false,
            interactive: false,
        });

        new mapboxgl.Marker(el).setLngLat([lng, lat]).addTo(map);
    }, []);

    return (
        <div
            id="mapContainer"
            className={`${className}`}
            style={{ width: "100%", height: "275px" }}
        />
    );
};
