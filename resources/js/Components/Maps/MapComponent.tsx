import React, { useEffect, useRef, useState } from "react";

import Map, { ViewState, MapRef } from "react-map-gl/maplibre";
import { useRecoilState } from "recoil";

import { EventList } from "@/Components/API/EventList";
import { EventCountButton } from "@/Components/Buttons/EventCountButton";
import { GetUserLocationModal } from "@/Components/GetUserLocationModal";
import { IsGettingLocation } from "@/Components/IsGettingLocation";
import { IsLoading } from "@/Components/IsLoading";
import { GeolocateControlComponent } from "@/Components/Maps/GeolocateControlComponent";
import { OutsideClickHandlerEvents } from "@/Components/Ui/OutsideClickHandlerEvents";
import { defaultLatLng } from "@/helpers/defaultLatLng";
import { LOCATION_PERMISSIONS_DENIED } from "@/helpers/locationPermissions";
import { useGetEvents } from "@/Hooks/useGetEvents";
import { useLoaderState } from "@/Hooks/useLoaderState";
import { useMapData } from "@/Hooks/useMapData";
import { mapFlyTo } from "@/lib/mapFlyTo";
import { showFilterPanelState } from "@/store";
import { eventsListLoadingState } from "@/store/eventsAtom";
import {
    endDateStringState,
    radiusState,
    sexState,
    startDateStringState,
    typeGroupState,
    typesState,
} from "@/store/filtersAtom";
import { IMapComponentProps } from "@/types/IMapComponentProps";

export const MapComponent = ({ className = "" }: IMapComponentProps) => {
    const initialViewState = {
        longitude:
            Number(localStorage.getItem("longitude")) ??
            defaultLatLng.longitude,
        latitude:
            Number(localStorage.getItem("latitude")) ?? defaultLatLng.latitude,
        zoom: 10,
    };

    const mapContainerRef = useRef<HTMLDivElement>(null);
    const mapRef = useRef<MapRef>(null);
    const { latitude, setLatitude, longitude, setLongitude, selectedPlace } =
        useMapData();
    const [radius] = useRecoilState(radiusState);
    const [sex] = useRecoilState(sexState);
    const [typeGroup] = useRecoilState(typeGroupState);
    const [types] = useRecoilState(typesState);
    const [startDate] = useRecoilState(startDateStringState);
    const [endDate] = useRecoilState(endDateStringState);
    const [eventsListLoading] = useRecoilState(eventsListLoadingState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    const [openGetUserLocationModal, setOpenGetUserLocationModal] =
        useState(false);
    const { handleLoaderState } = useLoaderState();

    const mounted = useRef(false);
    const { getEvents } = useGetEvents();

    const handleViewStateChange = async (viewState: ViewState) => {
        if (!showFilterPanel) {
            const { latitude, longitude } = viewState;
            setLatitude(latitude);
            setLongitude(longitude);
        }
    };

    const handleFlyTo = async (theLatitude: number, theLongitude: number) => {
        if (mapRef.current) {
            mapFlyTo(mapRef, theLatitude, theLongitude);
        }
    };

    useEffect(() => {
        const storedLatitude = localStorage.getItem("latitude");
        const storedLongitude = localStorage.getItem("longitude");
        const locationPermissions = localStorage.getItem("locationPermissions");

        if (storedLatitude && storedLongitude) {
            console.log("user has coords in local storage, set them to state");
            console.log(mapRef);
            setLatitude(Number(storedLatitude));
            setLongitude(Number(storedLongitude));
            setTimeout(() => {
                mapFlyTo(
                    mapRef,
                    Number(storedLatitude),
                    Number(storedLongitude)
                );
            }, 1000);
        }

        if (
            !storedLatitude &&
            !storedLongitude &&
            locationPermissions !== LOCATION_PERMISSIONS_DENIED
        ) {
            console.log(
                "user has not stored location and has not denied location permissions, open modal to get location"
            );
            setOpenGetUserLocationModal(true);
        }

        if (locationPermissions === LOCATION_PERMISSIONS_DENIED) {
            console.log("location denied, get the default events");
            setLatitude(defaultLatLng.latitude);
            setLongitude(defaultLatLng.longitude);
        }
    }, []);

    useEffect(() => {
        if (!mounted.current) {
            mounted.current = true;
            return;
        }
        getEvents();
    }, [
        latitude,
        longitude,
        radius,
        sex,
        typeGroup,
        types,
        startDate,
        endDate,
    ]);

    useEffect(() => {
        handleLoaderState();
    }, [eventsListLoading]);

    useEffect(() => {
        if (selectedPlace && selectedPlace.center) {
            mapFlyTo(mapRef, selectedPlace.center[1], selectedPlace.center[0]);
        }
    }, [selectedPlace]);

    return (
        <div className={`${className} relative flex-1`} ref={mapContainerRef}>
            <Map
                ref={mapRef}
                initialViewState={initialViewState}
                minZoom={0}
                maxZoom={13}
                maxPitch={0}
                minPitch={0}
                pitchWithRotate={false}
                dragRotate={false}
                touchPitch={false}
                customAttribution={``}
                onDragEnd={async (e) => handleViewStateChange(e.viewState)}
                onZoomEnd={async (e) => handleViewStateChange(e.viewState)}
                style={{ width: "100%", height: "100%" }}
                mapStyle={`https://api.maptiler.com/maps/streets/style.json?key=sr5EVCtJBfz1IQnASSwn`}
            >
                <GetUserLocationModal
                    open={openGetUserLocationModal}
                    setOpen={setOpenGetUserLocationModal}
                    onLocationSet={handleFlyTo}
                />
                <EventList />

                <GeolocateControlComponent />
                <IsLoading
                    classnames={`relative mt-8`}
                    text={`Loading Events`}
                />
                <IsGettingLocation
                    classnames={`relative mt-8`}
                    text={`Triangulating location`}
                />
                <EventCountButton />
                <OutsideClickHandlerEvents />
            </Map>
        </div>
    );
};
