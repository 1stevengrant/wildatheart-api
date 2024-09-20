import React from "react";

import { MapRef } from "react-map-gl/maplibre";
import { useRecoilState } from "recoil";
import { selectedPlaceState } from "@/store/mapAtom";

export const mapFlyTo = (
    mapRef: React.RefObject<MapRef>,
    latitude: number,
    longitude: number
): void => {
    const [zoomLevel] = useRecoilState(selectedPlaceState);
    mapRef.current?.flyTo({
        essential: false,
        center: [longitude, latitude],
        duration: 2000,
        zoom: zoomLevel,
    });
};
