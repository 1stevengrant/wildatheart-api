import React, { useEffect, useRef, useState } from "react";

import axios from "axios";
import { ViewStateChangeEvent } from "react-map-gl";
import Map, {
    GeolocateControl,
    MapRef,
    ViewState,
} from "react-map-gl/maplibre";
import { useRecoilState, useSetRecoilState } from "recoil";

import { EventList } from "@/Components/API/EventList";
import { GetUserLocationModal } from "@/Components/Modals/GetUserLocationModal";
import { IsLoading } from "@/Components/Ui/IsLoading";
import { commonMapProps } from "@/helpers/commonMapProps";
import { useGetEventsInBounds } from "@/Hooks/useGetEventsInBounds";
import { useLoaderState } from "@/Hooks/useLoaderState";
import { boundsState } from "@/store/coordinatesAtom";
import { eventsListState } from "@/store/eventsAtom";

export const InteractiveMap = ({ className = "" }: { className?: string }) => {
    const mapContainerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<MapRef>(null);
    const setBounds = useSetRecoilState<any>(boundsState);
    const { showLoader } = useLoaderState();
    const { getEventsInBounds } = useGetEventsInBounds();

    const handleViewStateChange = async (event: ViewState) => {
        const newMapBounds = mapRef.current?.getBounds();
        setBounds(newMapBounds);
        await getEventsInBounds();
    };

    return (
        <div className={`${className} relative flex-1`} ref={mapContainerRef}>
            <Map
                ref={mapRef}
                {...commonMapProps}
                minZoom={0}
                maxZoom={13}
                onMoveEnd={(event) => handleViewStateChange(event.viewState)}
            >
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
