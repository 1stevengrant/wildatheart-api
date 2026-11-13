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
    mapRef: RefObject<MapRef>;
    onUserLocated: (latitude: number, longitude: number) => void;
};

const MAX_ZOOM_OUT_ATTEMPTS = 5;

const replaceTypesInUrl = (newTypes: number[]) => {
    const currentUrl = new URL(window.location.href);
    const currentTypesParam = currentUrl.searchParams.get("types");
    const newTypesParam = newTypes.length > 0 ? newTypes.join(",") : null;

    if (currentTypesParam === newTypesParam) {
        return;
    }

    const params = new URLSearchParams(currentUrl.search);

    if (newTypesParam) {
        params.set("types", newTypesParam);
    } else {
        params.delete("types");
    }

    const query = params.toString();

    router.replace({
        url: query
            ? `${window.location.pathname}?${query}`
            : window.location.pathname,
        preserveState: true,
        preserveScroll: true,
    });
};

export const LocalMap = ({ mapRef, onUserLocated }: LocalMapProps) => {
    const { searchEvents, showLoading } = useEvents();
    const zoomOutAttemptsRef = useRef(0);
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
    }, [setTypesTo]);

    useEffect(() => {
        if (initialLoadDone.current) {
            const timeoutId = setTimeout(() => {
                replaceTypesInUrl(types);
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
    }, [mapRef, selectedPlace]);

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
    }, [mapRef]);

    const onMapLoad = useCallback(() => {
        console.log("Map loaded");
        handleOnMapMoveEnd();
    }, [handleOnMapMoveEnd]);

    useEffect(() => {
        if (!bounds) {
            return;
        }

        const controller = new AbortController();

        searchEvents(
            bounds,
            { currentSex, types, typeGroup, startDateString, endDateString },
            controller.signal
        ).then((eventCount) => {
            if (eventCount === null) {
                return;
            }

            if (
                eventCount > 0 ||
                zoomOutAttemptsRef.current >= MAX_ZOOM_OUT_ATTEMPTS
            ) {
                zoomOutAttemptsRef.current = 0;

                return;
            }

            zoomOutAttemptsRef.current += 1;

            const map = mapRef.current?.getMap();

            // Zooming out ends with moveend, which searches the wider area.
            map?.zoomTo(map.getZoom() - 2);
        });

        return () => controller.abort();
    }, [
        bounds,
        currentSex,
        typeGroup,
        types,
        startDateString,
        endDateString,
        mapRef,
        searchEvents,
    ]);

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
