import { useEffect, useState, useRef } from "react";

import axios from "axios";
import Map, {
    GeolocateControl,
    LngLatBounds,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState, useSetRecoilState } from "recoil";

import { EventCountButton } from "@/Components/Buttons/EventCountButton";
import { LocalMapMarkers } from "@/Components/Maps/LocalMapMarkers";
import { IsLoading } from "@/Components/Ui/IsLoading";
import { OutsideClickHandlerEvents } from "@/Components/Ui/OutsideClickHandlerEvents";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";
import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";
import { getUserLocation, setUserLocation } from "@/lib/localStorage";
import { reactNotificationState } from "@/store";
import { latitudeState, longitudeState } from "@/store/coordinatesAtom";
import { eventsListState } from "@/store/eventsAtom";

const DEFAULT_LATITUDE = 0;
const DEFAULT_LONGITUDE = 0;

export const LocalMap = () => {
    const [reactNotifications, setReactNotifications] = useRecoilState(
        reactNotificationState
    );
    const [showLoading, setShowLoading] = useState(false);
    const { mapRef, selectedPlace } = useMapData();
    const [latitude, setLatitude] = useRecoilState(latitudeState);
    const [longitude, setLongitude] = useRecoilState(longitudeState);

    const [bounds, setBounds] = useState<LngLatBounds | null>(null);
    const setEvents = useSetRecoilState(eventsListState);
    const { currentSex } = useSex();
    const { typeGroup } = useTypeGroup();
    const { types } = useTypes();
    const { startDateString, endDateString } = useDates();

    const initialFlyToDone = useRef(false);
    const hasZoomedOut = useRef(false);

    useEffect(() => {
        const storedLocation = getUserLocation();
        console.log("Stored location:", storedLocation);
        if (storedLocation && !initialFlyToDone.current) {
            console.log("Setting latitude and longitude from stored location");
            setLatitude(storedLocation.latitude);
            setLongitude(storedLocation.longitude);
        } else {
            console.log("No stored location or initial fly already done");
        }
    }, []);

    useEffect(() => {
        console.log(
            "Latitude:",
            latitude,
            "Longitude:",
            longitude,
            "Map ref:",
            mapRef.current
        );
        if (
            mapRef.current &&
            latitude !== null &&
            longitude !== null &&
            !initialFlyToDone.current
        ) {
            console.log("Flying to location:", latitude, longitude);
            mapRef.current.getMap().flyTo({
                center: [longitude, latitude],
                zoom: 7,
                essential: true,
            });
            initialFlyToDone.current = true;
        }
    }, [latitude, longitude, mapRef.current]);

    // New effect to handle selectedPlace changes
    useEffect(() => {
        if (mapRef.current && selectedPlace) {
            console.log(
                "Flying to selected place:",
                selectedPlace.center[0],
                selectedPlace.center[1]
            );
            mapRef.current.getMap().flyTo({
                center: [selectedPlace.center[0], selectedPlace.center[1]],
                zoom: 7, // You can adjust this zoom level as needed
                essential: true,
            });
        }
    }, [selectedPlace]);

    const handleOnMapMoveEnd = () => {
        const map = mapRef.current?.getMap();
        if (map) {
            const newBounds = map.getBounds();
            setBounds(newBounds);
            const center = map.getCenter();
            console.log("Map moved to:", center.lat, center.lng);
            setUserLocation(center.lat, center.lng);
            setLatitude(center.lat);
            setLongitude(center.lng);
        }
    };

    const onMapLoad = () => {
        console.log("Map loaded");
        const map = mapRef.current?.getMap();
        if (map) {
            const newBounds = map.getBounds();
            setBounds(newBounds);
        }
    };

    useEffect(() => {
        if (bounds) {
            void getEvents();
        }
    }, [bounds, currentSex, typeGroup, types, startDateString, endDateString]);

    const handleGeolocate = (e: GeolocationPosition) => {
        const { latitude, longitude } = e.coords;
        setLatitude(latitude);
        setLongitude(longitude);
        setUserLocation(latitude, longitude);
        mapRef.current?.getMap().flyTo({
            center: [longitude, latitude],
            zoom: 7,
            essential: true,
        });
    };

    const getEvents = async () => {
        if (bounds) {
            const ne = bounds.getNorthEast();
            const sw = bounds.getSouthWest();
            try {
                setShowLoading(true);
                const response = await axios.get(route("api.events.search"), {
                    params: {
                        sw_latitude: sw.lat,
                        sw_longitude: sw.lng,
                        ne_latitude: ne.lat,
                        ne_longitude: ne.lng,
                        sex: currentSex,
                        types: types.join(","),
                        type_group: typeGroup,
                        start_date: startDateString,
                        end_date: endDateString,
                    },
                });
                setShowLoading(false);
                if (response.data.length === 0 && !hasZoomedOut.current) {
                    mapRef.current
                        ?.getMap()
                        .zoomTo(mapRef.current?.getMap().getZoom() - 1);
                    hasZoomedOut.current = true;
                } else {
                    setEvents(response.data);
                }
            } catch (error) {
                console.log(error);
                setShowLoading(false);
            }
        }
    };

    return (
        <Map
            initialViewState={{
                latitude: latitude ?? DEFAULT_LATITUDE,
                longitude: longitude ?? DEFAULT_LONGITUDE,
                zoom: 3,
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
