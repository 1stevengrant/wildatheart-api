import React, { useEffect, useRef, useState } from "react";

import { useForm } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
import mapboxgl from "mapbox-gl";
import Map, {
    GeolocateControl,
    MapLayerMouseEvent,
    Marker,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState } from "recoil";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import FireNamingGuidelines from "@/Components/Events/FireNamingGuidelines";
import InputError from "@/Components/Forms/InputError";
import InputLabel from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import { SwitchComponent } from "@/Components/SwitchComponent";
import Spinner from "@/Components/Ui/Spinner";
import config from "@/helpers/config";
import lookupLocation from "@/helpers/lookupLocation";
import objectIsEmpty from "@/helpers/objectIsEmpty";
import { oneHour } from "@/helpers/timeConstants";
import { useDates } from "@/Hooks/useDates";
import useTypedPage from "@/Hooks/useTypedPage";
import { selectedPlaceState, zoomState } from "@/store/mapAtom";
import { EventFormDataType } from "@/types/EventFormDataType";

export const EventInformation = ({ user }: any) => {
    const [isPrivate, setIsPrivate] = useState(false);
    const mapRef = useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const [marker, setMarker] = useState<any>(null);
    const [userZoomLevel, setUserZoomLevel] = useRecoilState<number>(zoomState);

    const handleUserLevelZoom = (zoom: number) => {
        setUserZoomLevel(zoom);
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
                    userZoomLevel
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
            userZoomLevel
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
            is_private: false,
            start_date: null,
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

        const country = location.find((feature: { id: string | string[] }) =>
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
        console.log(data);
        post(
            route("host.eventType.create.store", { eventType: eventType.slug }),
            {
                preserveScroll: true,
            }
        );
    };

    const page = useTypedPage();
    const eventType = page.props.eventType as any;
    const handleDateChange = (date: Date | null) => {
        setStartDate(date);

        if (date) {
            setStartDateString(date.toISOString().split("T")[0]);
            setData("start_date", date);
        } else {
            setData("start_date", null);
            resetAllDates();
        }
    };
    const maxDate = dayjs().add(1, "year").toDate();
    const { startDate, setStartDate, setStartDateString, resetAllDates } =
        useDates();
    return (
        <form onSubmit={handleSubmit}>
            {eventType.slug === "fires" && (
                <>
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
                            <div className={`text-white`}>Fire</div>
                        </div>
                        <Instruction>
                            This is the name that will be displayed on your Fire
                            page.
                        </Instruction>
                        <InputError message={errors.name} />
                    </div>
                    <FireNamingGuidelines />
                </>
            )}

            <MapboxSearch className={"mt-4"} />

            <div className={`w-full h-[300px] md:h-[600px] mt-4`}>
                <Map
                    mapLib={mapboxgl}
                    ref={mapRef}
                    dragRotate={false}
                    touchPitch={false}
                    pitchWithRotate={false}
                    maxPitch={0}
                    minPitch={0}
                    mapStyle="mapbox://styles/mapbox/navigation-day-v1"
                    initialViewState={{
                        zoom: 6,
                    }}
                    style={{
                        width: "100%",
                        height: "100%",
                        borderRadius: "0.5rem",
                    }}
                    onClick={(event) => {
                        handleMapClick(event);
                    }}
                    mapboxAccessToken={config("services.mapbox.token")}
                    onZoomEnd={() =>
                        handleUserLevelZoom(mapRef.current?.getZoom() || 6)
                    }
                    attributionControl={false}
                >
                    <GeolocateControl position="top-left" />
                    <NavigationControl position="top-left" />
                    <>
                        {marker && (
                            <Marker
                                anchor={"bottom"}
                                longitude={marker.lng}
                                latitude={marker.lat}
                            />
                        )}
                    </>
                </Map>
            </div>

            {eventType.slug !== "fires" && (
                <div className={`mt-4`}>
                    <DatePickerInput
                        label="Start date"
                        placeholder="Select start date"
                        minDate={new Date()}
                        value={startDate}
                        maxDate={maxDate}
                        clearable
                        onChange={(value) => handleDateChange(value)}
                    />
                    <InputError message={errors.start_date} />

                    <label
                        className={`flex items-center space-x-4 hover:cursor-pointer`}
                    >
                        <SwitchComponent
                            checked={isPrivate}
                            onChange={() => {
                                setIsPrivate(!isPrivate);
                                setData("is_private", !isPrivate);
                            }}
                        />
                        <div className={`text-white text-sm mt-4`}>
                            <div>I want this event to be private.</div>
                            <div>
                                The event will not be shown on map or directory.
                            </div>
                        </div>
                    </label>
                </div>
            )}

            <div className="mt-4 space-y-4">
                <PrimaryButton
                    disabled={event.location === "Location TBC" && processing}
                    className={`w-full flex items-center justify-center`}
                >
                    {processing ? <Spinner /> : null}
                    {event.location === "Location TBC"
                        ? "Pick a location to continue"
                        : "Create Event & See Dashboard"}
                </PrimaryButton>
            </div>
        </form>
    );
};
