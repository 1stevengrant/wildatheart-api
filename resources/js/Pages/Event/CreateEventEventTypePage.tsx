import React, { useEffect, useRef, useState } from "react";

import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
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
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import SelectInput from "@/Components/Forms/SelectInput";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { ConfirmEventDetailsModal } from "@/Components/Modals/ConfirmEventDetailsModal";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { Spinner } from "@/Components/Ui/Spinner";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { commonMapProps } from "@/helpers/commonMapboxMapProps";
import { LOCATION_TBC } from "@/helpers/locationTbc";
import { lookupLocation } from "@/helpers/lookupLocation";
import { objectIsEmpty } from "@/helpers/objectIsEmpty";
import { scrollToError } from "@/helpers/scrollToError";
import { oneHour } from "@/helpers/timeConstants";
import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import useTypedPage from "@/Hooks/useTypedPage";
import { SidebarLayout } from "@/Layouts/SidebarLayout";
import { selectedPlaceState } from "@/store/mapAtom";

interface PageProps {
    eventType: App.Data.EventTypeData;
    eventSteps: any;
    materialOrderItems: { value: string; label: string }[];
    [key: string]: any;
}

const CreateEventEventTypePage = () => {
    const page = useTypedPage();
    const { materialOrderItems } = usePage<PageProps>().props;

    const { user } = page.props.auth as any;

    const [isPrivate, setIsPrivate] = useState(false);
    const mapRef = useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const [marker, setMarker] = useState<any>(null);
    const { zoom, handleUserLevelZoom } = useMapData();

    const [confirmModalOpen, setConfirmModalOpen] = useState(false);

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
        flyTo([0, 0], undefined, zoom);
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
        host: user.fullname,
        host_photo: user.image,
        country: null,
    });

    const { data, setData, post, processing, errors } =
        useForm<App.Data.EventFormData>({
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
            number_of_items_in_material_order: null,
            end_date: null,
            cadence: null,
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

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();
        setConfirmModalOpen(true);
    };

    const confirmSubmit = () => {
        setConfirmModalOpen(false);
        post(
            route("host.eventType.create.store", { eventType: eventType.slug }),
            {
                preserveScroll: true,
                onError: () => scrollToError(),
            }
        );
    };

    const eventType = page.props.eventType as any;

    const { startDate, handleStartDateChange, maxDate } = useDates();

    return (
        <>
            <Head title={`Create Event`} />
            <SidebarLayout
                footer={
                    <EmptyLayoutFooter>
                        <Link
                            href={route("host.eventType.mailing", eventType)}
                            className={circularButtonClasses}
                        >
                            <ArrowLeftIcon className="h-4 w-4" />
                        </Link>
                        <PrimaryButton
                            onClick={handleSubmit}
                            disabled={
                                processing || event.location === LOCATION_TBC
                            }
                        >
                            {processing ? <Spinner /> : null}
                            {event.location === LOCATION_TBC
                                ? "Pick a location to continue"
                                : "Create Event & See Dashboard"}
                            <ArrowRightIcon className="ml-2 h-4 w-4" />
                        </PrimaryButton>
                    </EmptyLayoutFooter>
                }
            >
                <EmptyLayoutBody>
                    <PageContainer>
                        <HeadingOne>Create Event</HeadingOne>
                        <p>
                            The below information can be changed at any time.
                            Once published, attendees will be able to see your
                            event on the directory map and reach out to you for
                            more info.
                        </p>
                        <form>
                            <MapboxSearch className={"mt-4"} />

                            <div
                                className={`w-full h-[300px] md:h-[40vh] mt-4`}
                            >
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
                                                anchor={"bottom"}
                                                longitude={marker.lng}
                                                latitude={marker.lat}
                                            />
                                        )}
                                    </>
                                </Map>
                            </div>

                            <div className={`mt-4`}>
                                <DatePickerInput
                                    label="Start date"
                                    placeholder="Select start date"
                                    minDate={new Date()}
                                    value={startDate}
                                    maxDate={maxDate}
                                    clearable
                                    required
                                    onChange={(date) =>
                                        handleStartDateChange(date, setData)
                                    }
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
                                        <div>
                                            I want this event to be private.
                                        </div>
                                        <div>
                                            The event will not be shown on map
                                            or directory.
                                        </div>
                                    </div>
                                </label>
                                {eventType.allow_materials_order && (
                                    <div className={`mt-4`}>
                                        <p>
                                            We'll send you free{" "}
                                            {singular(eventType.name)}{" "}
                                            note-taking guides for your
                                            attendees. Just tell us how many you
                                            need.
                                        </p>

                                        <InputLabel
                                            htmlFor="number_of_items_in_material_order"
                                            value={
                                                eventType.number_of_items_in_material_order_label ??
                                                "Number of items in material order"
                                            }
                                            required
                                        />
                                        <SelectInput
                                            id={
                                                "number_of_items_in_material_order"
                                            }
                                            onChange={(
                                                e: React.ChangeEvent<
                                                    | HTMLInputElement
                                                    | HTMLSelectElement
                                                >
                                            ) => {
                                                const value = e.target.value;
                                                setData(
                                                    "number_of_items_in_material_order",
                                                    value
                                                );
                                            }}
                                            className={`text-white mt-2`}
                                            options={materialOrderItems}
                                        />
                                        <InputError
                                            message={
                                                errors.number_of_items_in_material_order
                                            }
                                        />
                                    </div>
                                )}
                            </div>
                        </form>
                    </PageContainer>
                </EmptyLayoutBody>

                <ConfirmEventDetailsModal
                    open={confirmModalOpen}
                    setOpen={setConfirmModalOpen}
                    data={data}
                    eventLocation={event.location}
                    processing={processing}
                    onConfirm={confirmSubmit}
                />
            </SidebarLayout>
        </>
    );
};

export default CreateEventEventTypePage;
