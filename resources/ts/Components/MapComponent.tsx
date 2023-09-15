import Map, {GeolocateControl, NavigationControl, Marker, MapRef} from "react-map-gl";
import {comparePosition} from "../lib/comparePosition";
import React, {useEffect, useRef, useState} from "react";
import {GeoSearchProvided} from "react-instantsearch-core";
import {connectGeoSearch} from "react-instantsearch-dom";
import mapboxgl from "mapbox-gl";
import FireMarker from "./FireMarker";
import {useRecoilState} from "recoil";
import {currentFireAtom, flyToLocationAtom, searchAsMovingAtom, selectedPlaceAtom} from "../store";
import {GeoHit} from "../types/FireHit";
import SelectedFire from "./SelectedFire";

const longitude = document.getElementById('longitude') as HTMLInputElement,
    latitude = document.getElementById('latitude') as HTMLInputElement;

const MapComponent: React.FC<GeoSearchProvided> = ({
                                                       hits,
                                                       refine,
                                                   }) => {
    const mapRef = useRef<MapRef>(null);
    const [userInteractionEnabled, setUserInteractionEnabled] = useState(true);
    const [checked] = useRecoilState(searchAsMovingAtom)
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom);
    const [selectedPlace, setSelectedPlace] = useRecoilState(selectedPlaceAtom);
    const [flyToLocation, setFlyToLocation] = useRecoilState(flyToLocationAtom)

    useEffect(() => {
        setUserInteractionEnabled(checked)
    }, [checked]);

    useEffect(() => {
        if (selectedPlace) {
            flyTo(selectedPlace.center)
        }

    }, [selectedPlace, flyToLocation]);

    const flyTo = (coordinates: [number, number]) => {
        mapRef.current?.flyTo({
            essential: false,
            center: coordinates,
            duration: 2000,
            zoom: 6
        })
    }

    const handleMarkerClick = (marker: GeoHit) => {
        const {lat, lng} = marker._geoloc
        setCurrentFire(marker)
        flyTo([lng, lat])
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
             attributionControl={false}
             mapStyle="mapbox://styles/mapbox/navigation-day-v1"
             initialViewState={{
                 zoom: 1,
                 latitude: parseFloat(latitude.value),
                 longitude: parseFloat(longitude.value),
             }}
             style={{width: '100%', height: '100%'}}
             mapboxAccessToken={process.env.MIX_REACT_APP_MAPBOX_TOKEN}
        >
            <>
                {hits.map(hit => (
                    <Marker
                        style={comparePosition([currentFire?._geoloc.lng, currentFire?._geoloc.lat], [hit._geoloc.lng, hit._geoloc.lat]) ? {zIndex: 1000} : {zIndex: 0}}
                        key={hit.objectID}
                        latitude={hit._geoloc.lat}
                        anchor="bottom"
                        longitude={hit._geoloc.lng}>
                        <>
                            {comparePosition([currentFire?._geoloc.lng, currentFire?._geoloc.lat], [hit._geoloc.lng, hit._geoloc.lat]) ?
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
