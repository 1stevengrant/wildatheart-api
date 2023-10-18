import {MapRef} from "react-map-gl";

const flyTo = (
    mapRef: MapRef,
    coordinates: [number, number],
    duration: number = 2000,
    zoom: number = 6
) => {
    mapRef.flyTo({
        essential: false,
        center: coordinates,
        duration: duration,
        zoom: zoom
    })
}

export default flyTo
