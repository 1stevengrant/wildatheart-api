import React, { useEffect, useRef, useState } from "react";

import { MapPinIcon } from "@heroicons/react/24/solid";
import { Link, useForm } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
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
import { FireNamingGuidelines } from "@/Components/Events/FireNamingGuidelines";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import TextInput from "@/Components/Forms/TextInput";
import { FireMarker } from "@/Components/Icons/FireMarker";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { config } from "@/helpers/config";
import { eventIsAFire } from "@/helpers/eventIsAFire";
import { lookupLocation } from "@/helpers/lookupLocation";
import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import useRoute from "@/Hooks/useRoute";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { selectedPlaceState } from "@/store/mapAtom";
import { Event } from "@/types/Event";

const EditEventPage = () => {
    const { startDate, setStartDate, resetAllDates, maxDate } = useDates();
    const mapRef = useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const { handleUserLevelZoom } = useMapData();
    const [isPrivate, setIsPrivate] = useState(false);

    const page = useTypedPage();
    const user = page.props.auth.user!;
    const event = page.props.event as Event;
    const eventTypeName = event.event_type_data.name;
    const route = useRoute();

    const pageTitle = `Editing ${event.name}`;

    const [marker, setMarker] = useState<any>({
        lat: event.latitude,
        lng: event.longitude,
    });

    const { data, setData, errors, put } = useForm({
        name: event.name || `${user.given_name}'s`,
        state: event.state,
        latitude: event.latitude,
        longitude: event.longitude,
        country: event.country,
        region: event.region,
        district: event.district,
        locality: event.locality,
        neighborhood: event.neighborhood,
        street_address: event.street_address,
        city: event.city,
        zip: event.zip,
        is_private: event.is_private,
        start_date: event.start_date,
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

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route("update.event", event.id));
    };

    const handleMapClick = (event: MapLayerMouseEvent) => {
        const { lat, lng } = event.lngLat;
        addEvent(lng, lat).then((r) => console.log(r));
    };

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
            )?.text;

        data.country = country;
        data.region = region;
        data.state = region;
        data.district = district;
        data.locality = locality;
        data.neighborhood = neighborhood;
        data.street_address = streetAddress;
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
    };

    useEffect(() => {
        setTimeout(() => {
            flyTo([event.longitude, event.latitude], 0, 6);
        }, 50);

        dayjs(event.start_date).isValid() &&
            setStartDate(dayjs(event.start_date).toDate());

        setIsPrivate(event.is_private);
    }, []);

    const handleDateChange = (date: Date | null) => {
        setStartDate(date);

        if (date) {
            const transformedDate = dayjs(date).format("YYYY-MM-DD");
            setData("start_date", transformedDate);
        } else {
            setData("start_date", "");
            resetAllDates();
        }
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

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>
            <form onSubmit={handleSubmit}>
                <div>
                    <InputLabel
                        htmlFor="name"
                        value={`Name of ${eventTypeName}`}
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
                            }}
                        />
                        {eventIsAFire(event.event_type) && <div>Fire</div>}
                    </div>
                    {eventIsAFire(event.event_type) && (
                        <Instruction>
                            This is the name that will be displayed on your Fire
                            page.
                        </Instruction>
                    )}
                    <InputError message={errors.name} />
                </div>
                {eventIsAFire(event.event_type) && <FireNamingGuidelines />}

                <MapboxSearch className={"mt-4"} />
                <div className="mt-4 w-full h-[300px] md:h-[600px]">
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
                        onZoomEnd={() => {
                            handleUserLevelZoom(mapRef.current?.getZoom() || 6);
                        }}
                        attributionControl={false}
                    >
                        <GeolocateControl position="top-left" />
                        <NavigationControl position="top-left" />
                        <>
                            {marker && (
                                <Marker
                                    longitude={marker.lng}
                                    latitude={marker.lat}
                                >
                                    {eventIsAFire(event.event_type) ? (
                                        <FireMarker className={"w-10 h-10"} />
                                    ) : (
                                        <MapPinIcon
                                            className={"w-10 h-10 text-p200"}
                                        />
                                    )}
                                </Marker>
                            )}
                        </>
                    </Map>
                </div>
                {!eventIsAFire(event.event_type) && (
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
                                    The event will not be shown on map or
                                    directory.
                                </div>
                            </div>
                        </label>
                    </div>
                )}
                <div className={`mt-4 space-x-4`}>
                    <PrimaryButton>
                        Update This {singular(eventTypeName)}
                    </PrimaryButton>
                    <Link
                        className={"text-n40 inline-block text-center"}
                        href={route("my.events.active")}
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </HostDashboardLayout>
    );
};

export default EditEventPage;
