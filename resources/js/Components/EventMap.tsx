import React from "react";

import { MapPinIcon } from "@heroicons/react/24/solid";
import Map, { GeolocateControl, NavigationControl, Marker } from "react-map-gl";

import { FireMarker } from "@/Components/Icons/FireMarker";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";

interface EventMapProps {
    mapRef: React.RefObject<any>;
    marker: any;
    onMapClick: (event: any) => void;
    onZoomEnd: () => void;
    isFire?: boolean;
    className?: string;
    height?: string;
}

export const EventMap: React.FC<EventMapProps> = ({
    mapRef,
    marker,
    onMapClick,
    onZoomEnd,
    isFire = false,
    className = "",
    height = "h-[300px] md:h-[600px]",
}) => {
    return (
        <div className={`w-full ${height} ${className}`}>
            <Map
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
                        anchor={isFire ? undefined : "bottom"}
                    >
                        {isFire ? (
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
