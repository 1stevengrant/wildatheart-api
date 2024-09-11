import { useRef } from "react";

import { MapRef } from "react-map-gl/maplibre";
import { useRecoilState } from "recoil";

import { mapFlyTo } from "@/lib/mapFlyTo";
import {
    boundsState,
    latitudeState,
    longitudeState,
} from "@/store/coordinatesAtom";
import { selectedPlaceState } from "@/store/mapAtom";

export const useMapData = () => {
    const mapRef = useRef<MapRef>(null);
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);
    const [bounds, setBounds] = useRecoilState(boundsState);

    const [selectedPlace, setSelectedPlace] =
        useRecoilState(selectedPlaceState);

    return {
        latitude,
        longitude,
        setLatitude,
        setLongitude,
        setSelectedPlace,
        selectedPlace,
        mapFlyTo,
        mapRef,
        bounds,
        setBounds,
    };
};
