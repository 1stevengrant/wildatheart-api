import React, { useEffect, useRef, useState } from "react";

import Map, { GeolocateControl, ViewState } from "react-map-gl/maplibre";
import { useRecoilState, useSetRecoilState } from "recoil";

import { EventList } from "@/Components/API/EventList";
import { GetUserLocationModal } from "@/Components/Modals/GetUserLocationModal";
import { IsLoading } from "@/Components/Ui/IsLoading";
import { commonMapProps } from "@/helpers/commonMapProps";
import { useGetEventsInBounds } from "@/Hooks/useGetEventsInBounds";
import { useLoaderState } from "@/Hooks/useLoaderState";
import { useMapData } from "@/Hooks/useMapData";
import { useMapFlyTo } from "@/Hooks/useMapFlyTo";
import { showFilterPanelState } from "@/store";
import {
    boundsState,
    latitudeState,
    longitudeState,
} from "@/store/coordinatesAtom";

export const InteractiveMap = ({ className = "" }: { className?: string }) => {
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);
    const setBounds = useSetRecoilState<any>(boundsState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    const [openGetUserLocationModal, setOpenGetUserLocationModal] =
        useState(false);
    const { mapRef, setZoomLevel } = useMapData();
    const { showLoader } = useLoaderState();
    const { getEventsInBounds } = useGetEventsInBounds();
    const { mapFlyTo } = useMapFlyTo();

    const handleViewStateChange = async (event: ViewState) => {
        if (!showFilterPanel) {
            const { latitude, longitude, zoom } = event;
            setLatitude(latitude);
            setLongitude(longitude);
            setZoomLevel(zoom);

            const newMapBounds = mapRef.current?.getBounds();
            setBounds(newMapBounds);
            await getEventsInBounds();
        }
    };

    useEffect(() => {
        console.log(latitude, longitude);
        setOpenGetUserLocationModal(true);
    }, []);

    useEffect(() => {
        if (latitude && longitude) {
        }
    }, [latitude, longitude]);

    return (
        <div className={`${className} relative flex-1`} ref={mapContainerRef}>
            <Map
                ref={mapRef}
                {...commonMapProps}
                minZoom={0}
                maxZoom={13}
                onMoveEnd={(event) => handleViewStateChange(event.viewState)}
            >
                <GetUserLocationModal
                    open={openGetUserLocationModal}
                    setOpen={setOpenGetUserLocationModal}
                />
                <GeolocateControl position="top-left" />
                <EventList />
                {showLoader && (
                    <IsLoading
                        className={`relative mt-8`}
                        text={`Loading Events`}
                    />
                )}
            </Map>
        </div>
    );
};
