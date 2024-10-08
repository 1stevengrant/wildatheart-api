import { useRef } from "react";

import type { MapRef } from "react-map-gl";
import { useRecoilState } from "recoil";

import { latitudeState, longitudeState } from "@/store/coordinatesAtom";
import { selectedPlaceState, zoomState } from "@/store/mapAtom";

export const useMapData = () => {
    const mapRef = useRef<MapRef>(null);
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);
    const [selectedPlace, setSelectedPlace] =
        useRecoilState(selectedPlaceState);
    const [zoom, setZoom] = useRecoilState(zoomState);

    const handleUserLevelZoom = (level: number) => {
        setZoom(level);
    };

    return {
        latitude,
        longitude,
        setLatitude,
        setLongitude,
        setSelectedPlace,
        selectedPlace,
        mapRef,
        zoom,
        setZoom,
        handleUserLevelZoom,
    };
};
