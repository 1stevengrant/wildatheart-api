import React, { useEffect } from 'react';
import mapboxgl from 'mapbox-gl';
import config from "@/helpers/config";

interface IStaticMapProps {
    lat: number;
    lng: number;
}

const StaticMap = ({ lat, lng}: IStaticMapProps) => {
    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN;
        const el = document.createElement("div");
        el.className = "custom-marker";
        el.style.backgroundImage = `url(/svgs/map-campfire-marker.svg)`;
        el.style.width = "50px";
        el.style.height = "50px";

        const map = new mapboxgl.Map({
            container: 'mapContainer',
            style: config('services.mapbox.style'),
            center: [lng, lat], // coordinates of the marker (lon, lat)
            zoom: 9,
            attributionControl: false,
            interactive: false,
        });

        new mapboxgl.Marker(el).setLngLat([lng, lat]).addTo(map);
    }, []);

    return <div id="mapContainer" style={{ width: '100%', height: '400px' }} />;
};

export default StaticMap;
