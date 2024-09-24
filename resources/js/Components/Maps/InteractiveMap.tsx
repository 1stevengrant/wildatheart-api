import React, { useRef } from "react";

import Map, { GeolocateControl } from "react-map-gl/maplibre";

import { EventList } from "@/Components/API/EventList";
import { commonMapProps } from "@/helpers/commonMapProps";
import { useMapData } from "@/Hooks/useMapData";

export const InteractiveMap = ({ className = "" }: { className?: string }) => {
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const { mapRef } = useMapData();
    return (
        <div className={`${className} relative flex-1`} ref={mapContainerRef}>
            <Map ref={mapRef} {...commonMapProps} minZoom={0} maxZoom={13}>
                {/*<GetUserLocationModal*/}
                {/*    open={openGetUserLocationModal}*/}
                {/*    setOpen={setOpenGetUserLocationModal}*/}
                {/*/>*/}
                <GeolocateControl position="top-left" />
                <EventList />
                {/*{showLoader && (*/}
                {/*    <IsLoading*/}
                {/*        className={`relative mt-8`}*/}
                {/*        text={`Loading Events`}*/}
                {/*    />*/}
                {/*)}*/}
            </Map>
        </div>
    );
};
