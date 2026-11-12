import { useState, useEffect, useRef } from "react";

import { lookupLocation } from "@/helpers/lookupLocation";
import { objectIsEmpty } from "@/helpers/objectIsEmpty";
import { oneHour } from "@/helpers/timeConstants";
import { useMapData } from "@/Hooks/useMapData";
import { MapboxPlace } from "@/types/MapboxPlace";

interface LocationData {
    country?: string;
    region?: string;
    state?: string;
    district?: string;
    place?: string;
    locality?: string;
    neighborhood?: string;
    street_address?: string;
    poi?: string;
    city?: string;
    zip?: string;
    latitude?: number;
    longitude?: number;
}

interface UseEventLocationProps {
    initialLocation?: {
        latitude: number;
        longitude: number;
    };
    onLocationChange?: (locationData: LocationData) => void;
}

export const useEventLocation = ({
    initialLocation,
    onLocationChange,
}: UseEventLocationProps = {}) => {
    const mapRef = useRef<any>(null);
    const [marker, setMarker] = useState<any>(
        initialLocation
            ? {
                  lat: initialLocation.latitude,
                  lng: initialLocation.longitude,
              }
            : null
    );
    const { zoom, handleUserLevelZoom } = useMapData();

    const flyTo = (
        coordinates: [number, number],
        duration: number = 2000,
        zoomLevel: number = 6
    ) => {
        mapRef.current?.flyTo({
            essential: false,
            center: coordinates,
            duration: duration,
            zoom: zoomLevel,
        });
    };

    const lookupCurrentLocation = () => {
        if (navigator.geolocation) {
            const locationData = JSON.parse(
                localStorage.getItem("locationData") || "{}"
            );

            if (
                !(
                    objectIsEmpty(locationData) ||
                    Date.now() - locationData.timestamp > oneHour
                )
            ) {
                flyTo(
                    [
                        locationData.coords.longitude,
                        locationData.coords.latitude,
                    ],
                    undefined,
                    zoom
                );
                addEvent(
                    locationData.coords.longitude,
                    locationData.coords.latitude
                );
            } else {
                navigator.geolocation.getCurrentPosition(
                    getLocationSuccess,
                    getLocationError
                );
            }
        }
    };

    const getLocationSuccess = (position: GeolocationPosition) => {
        localStorage.setItem(
            "locationData",
            JSON.stringify({
                timestamp: Date.now(),
                coords: {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                },
            })
        );
        flyTo(
            [position.coords.longitude, position.coords.latitude],
            undefined,
            zoom
        );

        addEvent(position.coords.longitude, position.coords.latitude);
    };

    const getLocationError = (error: GeolocationPositionError) => {
        console.log(error);
        flyTo([0, 0], undefined, initialLocation ? 6 : 1);
    };

    const addEvent = async (lng: number, lat: number) => {
        setMarker({ lat, lng });
        const location = await lookupLocation(lng, lat);

        let country = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("country")
        )?.text;
        const region = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("region")
        )?.text;
        const postcode = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("postcode")
        )?.text;
        const district = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("district")
        )?.text;
        const place = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("place")
        )?.text;
        const locality = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("locality")
        )?.text;
        const neighborhood = location.find(
            (feature: { id: string | string[] }) =>
                feature.id.includes("neighborhood")
        )?.text;
        const streetAddress = location.find(
            (feature: { id: string | string[] }) =>
                feature.id.includes("address")
        )?.text;
        const poi = location.find((feature: { id: string | string[] }) =>
            feature.id.includes("poi")
        )?.text;

        // Fallback: extract country from feature context (city-states like Singapore)
        if (!country) {
            const featureWithCountryContext = location.find(
                (feature: { context?: Array<{ id: string; text: string }> }) =>
                    feature.context?.some((ctx) => ctx.id.includes("country"))
            );
            country = featureWithCountryContext?.context?.find(
                (ctx: { id: string }) => ctx.id.includes("country")
            )?.text;
        }

        // Map US territories to United States
        const usTerritories = [
            "Puerto Rico",
            "Guam",
            "U.S. Virgin Islands",
            "American Samoa",
            "Northern Mariana Islands",
        ];
        if (country && usTerritories.includes(country)) {
            country = "United States";
        }

        const locationData: LocationData = {
            country,
            region,
            state: region, // Map region to state for backend compatibility
            district,
            place,
            locality,
            neighborhood,
            street_address: streetAddress,
            poi,
            latitude: lat,
            longitude: lng,
            city:
                place ??
                locality ??
                district ??
                region ??
                country ??
                "Middle of Nowhere",
            zip: postcode,
        };

        onLocationChange?.(locationData);

        return locationData;
    };

    const handleMapClick = (event: any) => {
        const { lat, lng } = event.lngLat;
        addEvent(lng, lat);
    };

    const selectPlace = (place: MapboxPlace) => {
        const [longitude, latitude] = place.center;

        flyTo([longitude, latitude], 2000, 6);
        addEvent(longitude, latitude);
    };

    // Initialize location on mount
    useEffect(() => {
        setTimeout(() => {
            if (initialLocation) {
                flyTo(
                    [initialLocation.longitude, initialLocation.latitude],
                    0,
                    6
                );
            } else {
                lookupCurrentLocation();
            }
        }, 50);
    }, []);

    return {
        mapRef,
        marker,
        flyTo,
        handleMapClick,
        handleUserLevelZoom,
        selectPlace,
        addEvent,
        lookupCurrentLocation,
    };
};
