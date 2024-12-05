import React, { useState, useEffect, useRef } from "react";

import { Link, useForm } from "@inertiajs/react";
import mapboxgl from "mapbox-gl";
import { singular } from "pluralize";
import Map, {
    GeolocateControl,
    MapLayerMouseEvent,
    Marker,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState } from "recoil";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventDisplay } from "@/Components/Events/EventDisplay";
import { FireNamingGuidelines } from "@/Components/Events/FireNamingGuidelines";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import { FireMarker } from "@/Components/Icons/FireMarker";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { Spinner } from "@/Components/Ui/Spinner";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";
import { EventTypeCaptivatingFires } from "@/helpers/eventNames";
import { lookupLocation } from "@/helpers/lookupLocation";
import { objectIsEmpty } from "@/helpers/objectIsEmpty";
import { scrollToError } from "@/helpers/scrollToError";
import { oneHour } from "@/helpers/timeConstants";
import { useMapData } from "@/Hooks/useMapData";
import useTypedPage from "@/Hooks/useTypedPage";
import { selectedPlaceState } from "@/store/mapAtom";
import { EventFormDataType } from "@/types/EventFormDataType";

export const FireInformation = ({ user }: any) => {
    const page = useTypedPage();
    const eventType = page.props.eventType as any;
    const mapRef = useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const [marker, setMarker] = useState<any>(null);
    const { handleUserLevelZoom, zoom } = useMapData();

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
        flyTo([0, 0], undefined, 1);
    };

    const flyTo = (
        coordinates: [number, number],
        duration: number = 2000,
        zoom: number = 6
    ) => {
        mapRef.current?.flyTo({
            essential: false,
            center: coordinates,
            duration: duration,
            zoom: zoom,
        });
    };

    const [event, setEvent] = useState<any>({
        name: user.given_name + "'s",
        location: "Location TBC",
        host: user.full_name,
        hostPhoto: user.image,
        country: null,
        eventType: eventType,
        hostEmail: user.email,
    });

    const { data, setData, post, processing, errors } =
        useForm<EventFormDataType>({
            name: `${user.given_name}'s`,
            state: "",
            city: "",
            zip: "",
            country: "",
            latitude: null,
            longitude: null,
            region: "",
            district: "",
            locality: "",
            neighborhood: "",
            street_address: "",
            poi: "",
        });

    useEffect(() => {
        if (selectedPlace) {
            mapRef.current?.flyTo({
                essential: false,
                center: selectedPlace.center,
                duration: 2000,
                zoom: 6,
            });
            const latitude = selectedPlace.center[1],
                longitude = selectedPlace.center[0];

            addEvent(longitude, latitude);
        }
    }, [selectedPlace]);

    useEffect(() => {
        setTimeout(() => {
            lookupCurrentLocation();
        }, 50);
    }, []);

    const addEvent = async (lng: number, lat: number) => {
        setMarker({ lat, lng });
        const location = await lookupLocation(lng, lat);

        let country = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("country")
            )?.text,
            region = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("region")
            )?.text,
            postcode = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("postcode")
            )?.text,
            district = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("district")
            )?.text,
            place = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("place")
            )?.text,
            locality = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("locality")
            )?.text,
            neighborhood = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("neighborhood")
            )?.text,
            streetAddress = location.find(
                (feature: { id: string | string[] }) =>
                    feature.id.includes("address")
            )?.text,
            poi = location.find((feature: { id: string | string[] }) =>
                feature.id.includes("poi")
            )?.text;

        // this is janky because Mapbox might return Puerto Rico as a region instead of a country
        // but this will work for now
        if (region === "Puerto Rico") {
            country = "United States";
        }

        data.country = country;
        data.region = region;
        data.district = district;
        data.locality = locality;
        data.neighborhood = neighborhood;
        data.street_address = streetAddress;
        data.poi = poi;
        data.latitude = lat;
        data.longitude = lng;
        data.city =
            place ??
            locality ??
            district ??
            region ??
            country ??
            "Middle of Nowhere";
        data.zip = postcode;

        setEvent({
            ...event,
            location:
                place ??
                locality ??
                district ??
                region ??
                country ??
                "Middle of Nowhere",
            country: country,
            region: region,
        });
    };

    const handleMapClick = (event: MapLayerMouseEvent) => {
        const { lat, lng } = event.lngLat;
        addEvent(lng, lat);
    };

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const eventPostEndpoint =
            eventType.slug === EventTypeCaptivatingFires
                ? route("host.captivating-fire.store")
                : route("host.fire.store");
        post(eventPostEndpoint, {
            preserveScroll: true,
            onError: () => scrollToError(),
        });
    };

    return (
        <TwoColumnWrapper>
            <LeftColumn>
                <form
                    onSubmit={handleSubmit}
                    className={"max-w-[500px] mx-auto"}
                >
                    <HeadingOne className={"my-12"}>
                        Tell us about your {singular(eventType.name)}
                    </HeadingOne>
                    <div>
                        <InputLabel
                            htmlFor="fire_name"
                            value="Name Your Fire"
                            required
                        />
                        <div className="flex items-center space-x-4">
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                autoComplete="off"
                                isFocused={true}
                                onChange={(e) => {
                                    setData("name", e.target.value);
                                    setEvent({
                                        ...event,
                                        name: e.target.value,
                                    });
                                }}
                            />
                            <div>Fire</div>
                        </div>
                        <Instruction>
                            This is the name that will be displayed on your Fire
                            page.
                        </Instruction>
                        <InputError message={errors.name} />
                    </div>
                    <FireNamingGuidelines />
                    <EventDisplay event={event} className={"md:hidden mt-4"} />
                    {/*{console.log(event)}*/}
                    <MapboxSearch className={"mt-4"} />
                    <div className={`w-full h-[300px] md:h-[600px] mt-4`}>
                        <Map
                            mapLib={mapboxgl}
                            ref={mapRef}
                            {...commonMapProps}
                            initialViewState={{
                                zoom: 6,
                            }}
                            onClick={(event) => {
                                handleMapClick(event);
                            }}
                            onZoomEnd={() =>
                                handleUserLevelZoom(
                                    mapRef.current?.getZoom() || 6
                                )
                            }
                        >
                            <GeolocateControl position="top-left" />
                            <NavigationControl position="top-left" />
                            <>
                                {marker && (
                                    <Marker
                                        longitude={marker.lng}
                                        latitude={marker.lat}
                                    >
                                        <FireMarker className={"w-10 h-10"} />
                                    </Marker>
                                )}
                            </>
                        </Map>
                    </div>

                    <div className="mt-4 space-y-4">
                        <PrimaryButton
                            disabled={
                                event.location === "Location TBC" && processing
                            }
                            className={`w-full flex items-center justify-center`}
                        >
                            {processing ? <Spinner /> : null}
                            {event.location === "Location TBC"
                                ? "Pick a location to continue"
                                : "Next"}
                        </PrimaryButton>
                        <Link
                            className={
                                "text-grey-200 w-full inline-block text-center"
                            }
                            href={route("home")}
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </LeftColumn>

            <RightColumn className={``}>
                <div>
                    <EventDisplay
                        className={"max-w-[350px] mx-auto hidden lg:flex"}
                        event={event}
                    />
                </div>
            </RightColumn>
        </TwoColumnWrapper>
    );
};
