import React from "react";

import { MapRef } from "react-map-gl/maplibre";

export const mapFlyTo = (
    mapRef: React.RefObject<MapRef>,
    latitude: number,
    longitude: number
): void => {
    mapRef.current?.flyTo({
        essential: false,
        center: [longitude, latitude],
        duration: 2000,
        zoom: 10,
    });
};
