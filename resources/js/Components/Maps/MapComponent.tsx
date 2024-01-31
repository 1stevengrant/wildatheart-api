import Map, {GeolocateControl, NavigationControl, Marker, MapRef} from "react-map-gl";
import {comparePosition} from "@/lib/comparePosition";
import React, {FC, useEffect, useRef, useState} from "react";
import {GeoSearchProvided} from "react-instantsearch-core";
import {connectGeoSearch} from "react-instantsearch-dom";
import mapboxgl from "mapbox-gl";
import {useRecoilState} from "recoil";
import {currentEventAtom, flyToLocationAtom, selectedPlaceAtom} from "@/store";
import objectIsEmpty from "@/helpers/objectIsEmpty";
import {oneHour} from "@/helpers/timeConstants";
import FiresMarker from "@/Components/Icons/FiresMarker";
import RetreatsMarker from "@/Components/Icons/RetreatsMarker";
import Tippy from '@tippyjs/react/headless';
import EventCard from "@/Components/Events/EventCard";
import {defaultLatLng} from "@/helpers/defaultLatLng";
import {GeoHit} from "@/types/EventHit";
import ExperiencesMarker from "@/Components/Icons/ExperiencesMarker";

const MapComponent: FC<GeoSearchProvided> = ({hits}) => {
    const mapRef = useRef<MapRef>(null);
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventAtom);
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
        flyTo([defaultLatLng.longitude, defaultLatLng.latitude], undefined, 9)
    }

    const iconClasses = `text-black inline-flex hover:stroke-white group-hover:stroke-white`
    const iconWrapperClasses = `group w-10 h-10 flex justify-center items-center rounded-full hover:bg-orange shadow-lg hover:cursor-pointer`

    const currentEventStyles = (hit: GeoHit) => {
        return currentEvent?.id === hit.id ? `stroke-white` : `stroke-black`
    }

    const [activeTooltipId, setActiveTooltipId] = useState('');

    const handleShowTooltip = (id: string) => {
        setActiveTooltipId(id);
    };

    const handleHideTooltip = () => {
        setActiveTooltipId('');
    };

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
                 latitude: defaultLatLng.latitude,
                 longitude: defaultLatLng.longitude
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
                            [currentEvent?._geoloc.lng, currentEvent?._geoloc.lat],
                            [hit._geoloc.lng, hit._geoloc.lat]) ?
                            {zIndex: 1000} :
                            {zIndex: 0}}
                        key={hit.objectID}
                        latitude={hit._geoloc.lat}
                        anchor="bottom"
                        longitude={hit._geoloc.lng}>

                        <Tippy trigger="click mouseenter"
                               key={hit.objectID}
                               placement={`bottom`}
                               interactive={true}
                               touch={false}
                               delay={250}
                               appendTo={() => document.body}
                               render={attrs => (
                                   <EventCard className={`hidden md:block`} event={hit}/>
                               )}>
                            <button
                                className={`${iconWrapperClasses} ${currentEvent?.id === hit.id ? `bg-orange text-white` : `bg-white`}`}
                                id={`fire-${hit.id}`}
                                onMouseEnter={() => handleShowTooltip(`fire-${hit.id}`)}
                                onMouseLeave={handleHideTooltip}
                                onClick={() => {
                                    setCurrentEvent(hit)
                                    handleShowTooltip(`fire-${hit.id}`);
                                }}>
                                {
                                    hit.eventType.includes('retreat') &&
                                    <RetreatsMarker className={`${iconClasses} ${currentEventStyles(hit)}`}/>
                                    || (hit.eventType.includes('experience') || hit.eventType.includes('school')) &&
                                    <ExperiencesMarker className={`${iconClasses} ${currentEventStyles(hit)}`}/>
                                    || <FiresMarker className={`${iconClasses} ${currentEventStyles(hit)}`}/>
                                }
                            </button>
                        </Tippy>
                    </Marker>
                ))}

            </>
            <GeolocateControl position="top-left"/>
            <NavigationControl position="top-left"/>
        </Map>
    )
};

export default connectGeoSearch(MapComponent);
