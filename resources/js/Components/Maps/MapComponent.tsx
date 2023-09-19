import Map, {GeolocateControl, NavigationControl, Marker, MapRef} from "react-map-gl";
import {comparePosition} from "@/lib/comparePosition";
import React, {FC, useEffect, useRef, useState} from "react";
import {GeoSearchProvided} from "react-instantsearch-core";
import {connectGeoSearch} from "react-instantsearch-dom";
import mapboxgl from "mapbox-gl";
import FireMarker from "@/Components/Fires/FireMarker";
import {useRecoilState} from "recoil";
import {currentFireAtom, flyToLocationAtom, selectedPlaceAtom} from "@/store";
import {GeoHit} from "@/types/FireHit";
import SelectedFire from "@/Components/Fires/SelectedFire";
import objectIsEmpty from "@/helpers/objectIsEmpty";
import {oneHour} from "@/helpers/timeConstants";

const MapComponent: FC<GeoSearchProvided> = ({hits}) => {
    const mapRef = useRef<MapRef>(null);
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom);
    const [selectedPlace, setSelectedPlace] = useRecoilState(selectedPlaceAtom);
    const [flyToLocation, setFlyToLocation] = useRecoilState(flyToLocationAtom)
    const [userZoomLevel, setUserZoomLevel] = useState<number>(6)

    useEffect(() => {
        setTimeout(() => {
            lookupCurrentLocation()
        }, 50);
    }, []);

    useEffect(() => {
        if (selectedPlace) {
            flyTo(selectedPlace.center)
        }
    }, [selectedPlace, flyToLocation]);

    const handleUserLevelZoom = (zoom: number) => {
        setUserZoomLevel(zoom)
        console.log(userZoomLevel)
    }

    const flyTo = (
        coordinates: [number, number],
        duration: number = 2000,
        zoom: number = 6
    ) => {
        mapRef.current?.flyTo({
            essential: false,
            center: coordinates,
            duration: duration,
            zoom: zoom
        })
    }

    const handleMarkerClick = (marker: GeoHit) => {
        const {lat, lng} = marker._geoloc
        setCurrentFire(marker)

        flyTo([lng, lat], undefined, userZoomLevel < 6 ? 6 : userZoomLevel)
    }

    const lookupCurrentLocation = () => {
        if (navigator.geolocation) {
            const locationData = JSON.parse(localStorage.getItem('locationData') || '{}');

            if (objectIsEmpty(locationData) || (Date.now() - locationData.timestamp) > oneHour) {
                navigator.geolocation.getCurrentPosition(getLocationSuccess, getLocationError);
            } else {
                flyTo([locationData.coords.longitude, locationData.coords.latitude], undefined, userZoomLevel)
            }
        }
    }

    const getLocationSuccess = (position: GeolocationPosition) => {
        localStorage.setItem('locationData', JSON.stringify({
            timestamp: Date.now(),
            coords: {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            }
        }));
        flyTo([position.coords.longitude, position.coords.latitude], undefined, userZoomLevel)
    }

    const getLocationError = (error: GeolocationPositionError) => {
        console.log(error)
        flyTo([0, 0], undefined, 1)
    }

    return (
        <Map mapLib={mapboxgl}
             ref={mapRef}
             maxZoom={10}
             dragRotate={false}
             touchPitch={false}
             pitchWithRotate={false}
             maxPitch={0}
             minPitch={0}
             mapStyle="mapbox://styles/mapbox/navigation-day-v1"
             initialViewState={{
                 zoom: userZoomLevel,
             }}
             style={{width: '100%', height: '100%'}}
             mapboxAccessToken={import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN}
             attributionControl={false}
             onZoomEnd={() => handleUserLevelZoom(mapRef.current?.getZoom() || 6)}
        >
            <>
                {hits.map(hit => (
                    <Marker
                        style={comparePosition(
                            [currentFire?._geoloc.lng, currentFire?._geoloc.lat],
                            [hit._geoloc.lng, hit._geoloc.lat]) ?
                            {zIndex: 1000} :
                            {zIndex: 0}}
                        key={hit.objectID}
                        latitude={hit._geoloc.lat}
                        anchor="bottom"
                        longitude={hit._geoloc.lng}>
                        <>
                            {comparePosition(
                                [currentFire?._geoloc.lng, currentFire?._geoloc.lat],
                                [hit._geoloc.lng, hit._geoloc.lat]) ?
                                <SelectedFire/> :
                                <FireMarker className={'hover:cursor-pointer w-8'}
                                            onClick={() => handleMarkerClick(hit)}/>
                            }
                        </>
                    </Marker>
                ))}
            </>
            <GeolocateControl position="top-left"/>
            <NavigationControl position="top-left"/>
        </Map>
    )
};

export default connectGeoSearch(MapComponent);
