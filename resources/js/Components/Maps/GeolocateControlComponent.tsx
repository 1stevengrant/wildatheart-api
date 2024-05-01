import { GeolocateControl } from "react-map-gl/maplibre";
import { useSetRecoilState } from "recoil";

import { useMapData } from "@/Hooks/useMapData";
import { searchingLocationState } from "@/store/coordinatesAtom";

export const GeolocateControlComponent = () => {
    const { setLatitude, setLongitude } = useMapData();
    const setSearchingLocation = useSetRecoilState(searchingLocationState);
    const handleTrackUserLocation = (e: any) => {
        setSearchingLocation(true);
        console.log(e);
    };
    const handleGeolocate = (e: any) => {
        const { latitude, longitude } = e.coords;
        console.log("geolocate");
        console.log(e.coords);
        setLongitude(latitude);
        setLatitude(longitude);
        localStorage.setItem("latitude", latitude);
        localStorage.setItem("longitude", longitude);
        setSearchingLocation(false);
    };
    const handleTrackUserLocationEnd = (e: any) => {
        console.log("track user location end");
        console.log(e);
    };
    return (
        <GeolocateControl
            trackUserLocation={true}
            showUserLocation={true}
            showAccuracyCircle={true}
            onTrackUserLocationStart={handleTrackUserLocation}
            onTrackUserLocationEnd={handleTrackUserLocationEnd}
            onGeolocate={handleGeolocate}
            position="top-left"
        />
    );
};
