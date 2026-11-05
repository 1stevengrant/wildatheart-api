import { useRecoilState } from "recoil";

import { selectedPlaceState, zoomState } from "@/store/mapAtom";

export const useMapData = () => {
    const [selectedPlace, setSelectedPlace] =
        useRecoilState(selectedPlaceState);
    const [zoom, setZoom] = useRecoilState(zoomState);

    const handleUserLevelZoom = (level: number) => {
        setZoom(level);
    };

    return {
        setSelectedPlace,
        selectedPlace,
        zoom,
        setZoom,
        handleUserLevelZoom,
    };
};
