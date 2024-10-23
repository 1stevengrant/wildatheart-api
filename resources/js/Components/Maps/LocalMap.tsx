import { useEffect, useState, useRef, useCallback } from "react";

import mapboxgl from "mapbox-gl";
import Map, {
    GeolocateControl,
    LngLatBounds,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState } from "recoil";
import { useSetRecoilState } from "recoil";

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
import { reactNotificationState } from "@/store";
import { latitudeState, longitudeState } from "@/store/coordinatesAtom";

const MAP_CONSTANTS = {
    MAX_ZOOM_OUT_ATTEMPTS: 6,
    DEFAULT_ZOOM_LEVEL: 3,
    SELECTED_PLACE_ZOOM_LEVEL: 7,
    GEOLOCATION_ZOOM_LEVEL: 4,
    ZOOM_OUT_INCREMENT: 1,
} as const;

export const LocalMap = () => {
    const { mapRef, selectedPlace } = useMapData();
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);
    const [currentZoom, setCurrentZoom] = useState<number | null>(null);
    const [mapBounds, setMapBounds] = useState<LngLatBounds | null>(null);
    const setReactNotifications = useSetRecoilState(reactNotificationState);

    const { currentSex } = useSex();
    const { typeGroup } = useTypeGroup();
    const { types } = useTypes();
    const { startDateString, endDateString } = useDates();
    const { getEvents, showLoading } = useEvents();

    const hasInitialFlyToDone = useRef(false);
    const zoomOutAttemptCount = useRef(0);
    const isAutomaticZoom = useRef(false);
    const [hasWebGLSupport, setHasWebGLSupport] = useState(true);
    const currentRequestController = useRef<AbortController | null>(null);

    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_APP_MAPBOX_ACCESS_TOKEN;

        if (!mapboxgl.supported()) {
            setHasWebGLSupport(false);
            return;
        }

        const savedLocation = getUserLocation();
        const savedZoom = getUserZoom();

        if (savedLocation && !hasInitialFlyToDone.current) {
            setLatitude(savedLocation.latitude);
            setLongitude(savedLocation.longitude);
        }

        if (savedZoom !== null) {
            setCurrentZoom(savedZoom);
        }
    }, []);

    const flyToLocation = useCallback(
        (lng: number, lat: number, zoomLevel: number) => {
            if (!mapRef.current) return;

            mapRef.current.getMap().flyTo({
                center: [lng, lat],
                zoom: zoomLevel,
                essential: true,
            });
        },
        []
    );

    useEffect(() => {
        if (
            !mapRef.current ||
            latitude === null ||
            longitude === null ||
            currentZoom === null ||
            hasInitialFlyToDone.current
        ) {
            return;
        }

        console.log(
            "Initial fly to:",
            latitude,
            longitude,
            "zoom:",
            currentZoom
        );
        flyToLocation(longitude, latitude, currentZoom);
        hasInitialFlyToDone.current = true;
    }, [latitude, longitude, currentZoom, mapRef.current]);

    useEffect(() => {
        if (!mapRef.current || !selectedPlace) return;

        console.log("Flying to selected place:", selectedPlace.center);
        flyToLocation(
            selectedPlace.center[0],
            selectedPlace.center[1],
            MAP_CONSTANTS.SELECTED_PLACE_ZOOM_LEVEL
        );
    }, [selectedPlace]);

    const handleMapMove = useCallback(() => {
        if (!mapRef.current) return;

        const map = mapRef.current.getMap();
        const newBounds = map.getBounds();
        const centerPosition = map.getCenter();
        const newZoomLevel = map.getZoom();

        console.log(
            "Map moved to:",
            centerPosition.lat,
            centerPosition.lng,
            "Zoom:",
            newZoomLevel
        );

        setMapBounds(newBounds);
        setUserLocation(centerPosition.lat, centerPosition.lng);
        setLatitude(centerPosition.lat);
        setLongitude(centerPosition.lng);
        setCurrentZoom(newZoomLevel);
        setUserZoom(newZoomLevel);

        // Only reset counter if it's not an automatic zoom
        if (!isAutomaticZoom.current) {
            zoomOutAttemptCount.current = 0;
        }
        isAutomaticZoom.current = false; // Reset the flag
    }, []);

    const handleMapLoad = useCallback(() => {
        console.log("Map loaded");
        handleMapMove();
    }, [handleMapMove]);

    useEffect(() => {
        if (!mapBounds) return;

        const fetchEventsForBounds = async () => {
            if (currentRequestController.current) {
                currentRequestController.current.abort();
            }

            currentRequestController.current = new AbortController();

            try {
                const eventCount = await getEvents(
                    mapBounds,
                    currentSex,
                    types,
                    typeGroup,
                    startDateString,
                    endDateString,
                    currentRequestController.current.signal
                );

                if (eventCount > 0) {
                    zoomOutAttemptCount.current = 0;
                    return;
                }

                if (
                    zoomOutAttemptCount.current >=
                    MAP_CONSTANTS.MAX_ZOOM_OUT_ATTEMPTS
                ) {
                    setReactNotifications((prevState) => [
                        ...prevState,
                        {
                            message:
                                "No events found in this area. Try searching in a different location.",
                            type: "error",
                        },
                    ]);
                    return;
                }

                const map = mapRef.current?.getMap();
                if (!map) return;

                const currentZoomLevel = map.getZoom();
                console.log(
                    `Zooming out attempt ${
                        zoomOutAttemptCount.current + 1
                    } of ${MAP_CONSTANTS.MAX_ZOOM_OUT_ATTEMPTS}`
                );
                isAutomaticZoom.current = true; // Set the flag before zooming
                map.zoomTo(currentZoomLevel - MAP_CONSTANTS.ZOOM_OUT_INCREMENT);
                zoomOutAttemptCount.current += 1;
            } catch (error: any) {
                if (error.name === "AbortError") {
                    console.log("Fetch aborted");
                    return;
                }
                console.error("Fetch error:", error);
            }
        };

        fetchEventsForBounds();

        return () => {
            currentRequestController.current?.abort();
        };
    }, [
        mapBounds,
        currentSex,
        typeGroup,
        types,
        startDateString,
        endDateString,
    ]);

    const handleGeolocate = useCallback((e: GeolocationPosition) => {
        const { latitude: userLat, longitude: userLng } = e.coords;
        setLatitude(userLat);
        setLongitude(userLng);
        setUserLocation(userLat, userLng);
        flyToLocation(userLng, userLat, MAP_CONSTANTS.GEOLOCATION_ZOOM_LEVEL);
    }, []);

    if (!hasWebGLSupport) return <NoWebGLSupport />;

    return (
        <Map
            initialViewState={{
                latitude: latitude ?? defaultLatLng.latitude,
                longitude: longitude ?? defaultLatLng.longitude,
                zoom: currentZoom ?? MAP_CONSTANTS.DEFAULT_ZOOM_LEVEL,
            }}
            {...commonMapProps}
            ref={mapRef}
            onMoveEnd={handleMapMove}
            onLoad={handleMapLoad}
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
