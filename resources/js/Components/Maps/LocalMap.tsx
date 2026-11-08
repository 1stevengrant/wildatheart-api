import { useEffect, useState, useRef, useCallback, RefObject } from "react";

import { router } from "@inertiajs/react";
import mapboxgl from "mapbox-gl";
import Map, {
    GeolocateControl,
    NavigationControl,
    LngLatBounds,
    MapRef,
} from "react-map-gl";
import { useRecoilState } from "recoil";

import { EventCountButton } from "@/Components/Buttons/EventCountButton";
import { FeaturedContentCard } from "@/Components/Maps/FeaturedContentCard";
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
import { featuredContentState } from "@/store/featuredContentAtom";

type LocalMapProps = {
    mapRef: RefObject<MapRef | null>;
    onUserLocated: (latitude: number, longitude: number) => void;
};

export const LocalMap = ({ mapRef, onUserLocated }: LocalMapProps) => {
    const { getEvents, showLoading, setZoomingComplete } = useEvents();
    const { selectedPlace } = useMapData();
    const [initialViewState] = useState(() => ({
        ...(getUserLocation() ?? defaultLatLng),
        zoom: getUserZoom() ?? 3,
    }));

    const [bounds, setBounds] = useState<LngLatBounds | null>(null);
    const [featuredContent] = useRecoilState(featuredContentState);
    const { currentSex } = useSex();
    const { typeGroup } = useTypeGroup();
    const { types, setTypesTo } = useTypes();
    const { startDateString, endDateString } = useDates();

    const [hasSupport, setHasSupport] = useState(true);
    const abortControllerRef = useRef<AbortController | null>(null);
    const initialLoadDone = useRef(false);

    useEffect(() => {
        if (!initialLoadDone.current) {
            const url = new URL(window.location.href);
            const typesParam = url.searchParams.get("types");

            if (typesParam) {
                const typeIds = typesParam.split(",").map((id) => parseInt(id));
                setTypesTo(typeIds);
            }
            initialLoadDone.current = true;
        }
    }, []);

    const debouncedUpdateUrl = useCallback((newTypes: number[]) => {
        const currentUrl = new URL(window.location.href);
        const currentTypesParam = currentUrl.searchParams.get("types");
        const newTypesParam = newTypes.length > 0 ? newTypes.join(",") : null;

        if (currentTypesParam !== newTypesParam) {
            const params = new URLSearchParams(currentUrl.search);

            if (newTypes.length > 0) {
                params.set("types", newTypes.join(","));
            } else {
                params.delete("types");
            }

            router.get(
                `${window.location.pathname}?${params.toString()}`,
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: [],
                }
            );
        }
    }, []);

    useEffect(() => {
        if (initialLoadDone.current) {
            const timeoutId = setTimeout(() => {
                debouncedUpdateUrl(types);
            }, 300);

            return () => clearTimeout(timeoutId);
        }
    }, [types]);

    useEffect(() => {
        mapboxgl.accessToken = import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN;

        if (!mapboxgl.supported()) {
            setHasSupport(false);
        }
    }, []);

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
        setUserZoom(newZoom);
        setZoomingComplete(); // Mark any zoom operation as complete
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
                    const result = await getEvents(
                        bounds,
                        currentSex,
                        types,
                        typeGroup,
                        startDateString,
                        endDateString,
                        abortControllerRef.current.signal
                    );

                    if (result.shouldZoomOut && mapRef.current) {
                        const currentZoom = mapRef.current.getMap().getZoom();
                        // Zoom out by 2 levels at a time for faster coverage
                        mapRef.current.getMap().zoomTo(currentZoom - 2);
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

    const handleGeolocate = useCallback(
        (e: GeolocationPosition) => {
            onUserLocated(e.coords.latitude, e.coords.longitude);
        },
        [onUserLocated]
    );

    if (!hasSupport) return <NoWebGLSupport />;

    return (
        <Map
            initialViewState={initialViewState}
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

            {/* Mobile only - featured content at top of map */}
            <FeaturedContentCard
                featuredContent={featuredContent}
                position="map-top"
            />
        </Map>
    );
};
