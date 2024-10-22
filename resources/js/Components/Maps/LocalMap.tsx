import { useEffect, useState, useRef, useCallback } from "react";

import mapboxgl from "mapbox-gl";
import Map, {
    GeolocateControl,
    LngLatBounds,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState } from "recoil";

import { EventCountButton } from "@/Components/Buttons/EventCountButton";
import { LocalMapMarkers } from "@/Components/Maps/LocalMapMarkers";
import { NoWebGLSupport } from "@/Components/Maps/NoWebGLSupport";
import { IsLoading } from "@/Components/Ui/IsLoading";
import { OutsideClickHandlerEvents } from "@/Components/Ui/OutsideClickHandlerEvents";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";
import { defaultLatLng } from "@/helpers/defaultLatLng";
import { useDates } from "@/Hooks/useDates";
import { useEvents } from "@/Hooks/useEvents";
import { useMapData } from "@/Hooks/useMapData";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";
import {
    getUserLocation,
    setUserLocation,
    getUserZoom,
    setUserZoom,
} from "@/lib/localStorage";
import { latitudeState, longitudeState } from "@/store/coordinatesAtom";

export const LocalMap = () => {
    const { mapRef, selectedPlace } = useMapData();
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);
    const [zoom, setZoom] = useState<number | null>(null);

    const [bounds, setBounds] = useState<LngLatBounds | null>(null);
    const { currentSex } = useSex();
    const { typeGroup } = useTypeGroup();
    const { types } = useTypes();
    const { startDateString, endDateString } = useDates();
    const { getEvents, showLoading } = useEvents();

    const initialFlyToDone = useRef(false);
    const hasZoomedOut = useRef(false);
    const [hasSupport, setHasSupport] = useState(true);
    const abortControllerRef = useRef<AbortController | null>(null);

    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_APP_MAPBOX_ACCESS_TOKEN;

        if (!mapboxgl.supported()) {
            setHasSupport(false);
        }

        const storedLocation = getUserLocation();
        const storedZoom = getUserZoom();

        if (storedLocation && !initialFlyToDone.current) {
            setLatitude(storedLocation.latitude);
            setLongitude(storedLocation.longitude);
        }

        if (storedZoom !== null) {
            setZoom(storedZoom);
        }

        return;
    }, []);

    useEffect(() => {
        if (
            mapRef.current &&
            latitude !== null &&
            longitude !== null &&
            zoom !== null &&
            !initialFlyToDone.current
        ) {
            console.log(
                "Flying to location:",
                latitude,
                longitude,
                "with zoom:",
                zoom
            );
            mapRef.current.getMap().flyTo({
                center: [longitude, latitude],
                zoom: zoom,
                essential: true,
            });
            initialFlyToDone.current = true;
        }
    }, [latitude, longitude, zoom, mapRef.current]);

    useEffect(() => {
        if (!mapRef.current || !selectedPlace) return;
        console.log(
            "Flying to selected place:",
            selectedPlace.center[0],
            selectedPlace.center[1]
        );
        mapRef.current.getMap().flyTo({
            center: selectedPlace.center,
            zoom: 7,
            essential: true,
        });
    }, [selectedPlace]);

    const handleOnMapMoveEnd = useCallback(() => {
        if (!mapRef.current) return;
        const map = mapRef.current.getMap();
        const newBounds = map.getBounds();
        setBounds(newBounds);
        const center = map.getCenter();
        const newZoom = map.getZoom();
        console.log("Map moved to:", center.lat, center.lng, "Zoom:", newZoom);
        setUserLocation(center.lat, center.lng);
        setLatitude(center.lat);
        setLongitude(center.lng);
        setZoom(newZoom);
        setUserZoom(newZoom);
    }, []);

    const onMapLoad = useCallback(() => {
        console.log("Map loaded");
        handleOnMapMoveEnd();
    }, [handleOnMapMoveEnd]);

    useEffect(() => {
        if (bounds) {
            const fetchEvents = async () => {
                if (abortControllerRef.current) {
                    abortControllerRef.current.abort();
                }

                abortControllerRef.current = new AbortController();

                try {
                    const eventCount = await getEvents(
                        bounds,
                        currentSex,
                        types,
                        typeGroup,
                        startDateString,
                        endDateString,
                        abortControllerRef.current.signal
                    );
                    if (eventCount === 0 && !hasZoomedOut.current) {
                        mapRef.current
                            ?.getMap()
                            .zoomTo(mapRef.current?.getMap().getZoom() - 1);
                        hasZoomedOut.current = true;
                    }
                } catch (error: any) {
                    if (error.name === "AbortError") {
                        console.log("Fetch aborted");
                    } else {
                        console.error("Fetch error:", error);
                    }
                }
            };
            fetchEvents();
        }

        return () => {
            if (abortControllerRef.current) {
                abortControllerRef.current.abort();
            }
        };
    }, [bounds, currentSex, typeGroup, types, startDateString, endDateString]);

    const handleGeolocate = useCallback((e: GeolocationPosition) => {
        const { latitude, longitude } = e.coords;
        setLatitude(latitude);
        setLongitude(longitude);
        setUserLocation(latitude, longitude);
        mapRef.current?.getMap().flyTo({
            center: [longitude, latitude],
            zoom: 4,
            essential: true,
        });
    }, []);

    if (!hasSupport) return <NoWebGLSupport />;

    return (
        <Map
            initialViewState={{
                latitude: latitude ?? defaultLatLng.latitude,
                longitude: longitude ?? defaultLatLng.longitude,
                zoom: zoom ?? 3,
            }}
            {...commonMapProps}
            ref={mapRef}
            onMoveEnd={handleOnMapMoveEnd}
            onLoad={onMapLoad}
        >
            <LocalMapMarkers />
            <GeolocateControl
                position="top-left"
                onGeolocate={handleGeolocate}
                trackUserLocation
            />
            <NavigationControl showCompass={false} position="top-left" />
            {showLoading && (
                <IsLoading
                    className={`relative mt-8`}
                    text={`Loading Events`}
                />
            )}

            <EventCountButton />
            <OutsideClickHandlerEvents />
        </Map>
    );
};
