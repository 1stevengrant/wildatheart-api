import { useEffect, useState } from "react";

import mapboxgl from "mapbox-gl";

import { NoWebGLSupport } from "@/Components/Maps/NoWebGLSupport";
import { config } from "@/helpers/config";


export const StaticMap = ({
    lat,
    lng,
    event,
    className = "",
}: {
    lat: number;
    lng: number;
    event: App.Data.EventData | null;
    className?: string;
}) => {
    const [mapIsSupported, setMapIsSupported] = useState(true);
    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN;
        if (!mapboxgl.supported()) {
            setMapIsSupported(false);
            return;
        }
        let mapMarkerUrl;
        console.log(event);
        if (event?.event_type.group_type.includes("retreat")) {
            mapMarkerUrl = "/svgs/map-marker-retreat.svg";
        } else if (event?.event_type.group_type.includes("experience")) {
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
            center: [lng, lat],
            zoom: 9,
            attributionControl: false,
            interactive: false,
        });

        new mapboxgl.Marker(el).setLngLat([lng, lat]).addTo(map);
    }, []);

    return (
        <>
            {!mapIsSupported ? (
                <NoWebGLSupport className={`my-24`} />
            ) : (
                <div
                    id="mapContainer"
                    className={`${className}`}
                    style={{ width: "100%", height: "275px" }}
                />
            )}
        </>
    );
};
