import React from "react";

import { MapPinIcon } from "@heroicons/react/24/solid";
import mapboxgl from "mapbox-gl";
import Map, { GeolocateControl, NavigationControl, Marker } from "react-map-gl";

import { FireMarker } from "@/Components/Icons/FireMarker";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";
import { eventIsAFire } from "@/helpers/eventIsAFire";

interface EventMapProps {
    mapRef: React.RefObject<any>;
    marker: any;
    onMapClick: (event: any) => void;
    onZoomEnd: () => void;
    eventTypeSlug?: string;
    className?: string;
    height?: string;
}

export const EventMap: React.FC<EventMapProps> = ({
    mapRef,
    marker,
    onMapClick,
    onZoomEnd,
    eventTypeSlug,
    className = "",
    height = "h-[300px] md:h-[600px]",
}) => {
    const isFireEvent = eventTypeSlug ? eventIsAFire(eventTypeSlug) : false;

    return (
        <div className={`w-full ${height} ${className}`}>
            <Map
                mapLib={mapboxgl}
                ref={mapRef}
                {...commonMapProps}
                initialViewState={{ zoom: 6 }}
                onClick={onMapClick}
                onZoomEnd={onZoomEnd}
            >
                <GeolocateControl position="top-left" />
                <NavigationControl position="top-left" />
                {marker && (
                    <Marker
                        longitude={marker.lng}
                        latitude={marker.lat}
                        anchor={isFireEvent ? undefined : "bottom"}
                    >
                        {isFireEvent ? (
                            <FireMarker className="w-10 h-10" />
                        ) : (
                            <MapPinIcon className="w-10 h-10 text-p200" />
                        )}
                    </Marker>
                )}
            </Map>
        </div>
    );
};
