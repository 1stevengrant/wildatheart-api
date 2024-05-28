import React, { useEffect, useRef, useState } from "react";

import { Link, useForm } from "@inertiajs/react";
import Parser from "html-react-parser";
import mapboxgl from "mapbox-gl";
import Map, {
    GeolocateControl,
    MapLayerMouseEvent,
    Marker,
    NavigationControl,
} from "react-map-gl";
import { useRecoilState, useSetRecoilState } from "recoil";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import FireMarker from "@/Components/Events/FireMarker";
import FireNamingGuidelines from "@/Components/Events/FireNamingGuidelines";
import InputError from "@/Components/Forms/InputError";
import InputLabel from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import config from "@/helpers/config";
import lookupLocation from "@/helpers/lookupLocation";
import useRoute from "@/Hooks/useRoute";
import useTypedPage from "@/Hooks/useTypedPage";
import { AppLayout } from "@/Layouts/AppLayout";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import { selectedPlaceState, zoomState } from "@/store/mapAtom";
import { Event } from "@/types/Event";

const EditEventPage = () => {
    const mapRef = useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const setUserZoomLevel = useSetRecoilState<number>(zoomState);
    const page = useTypedPage();
    const user = page.props.auth.user!;
    const event = page.props.event as Event;
    const route = useRoute();

    const publishedStatus = event.approved_at ? "published" : "unpublished";

    const pageTitle = `Editing ${event.name} Fire`;
    const pageName = `Editing ${event.name} Fire <span class="ml-2 rounded px-2 py-1 text-sm font-semibold uppercase text-white bg-orange">${publishedStatus}</span>`;
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
    }, []);

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

    const handleUserLevelZoom = (zoom: number) => {
        setUserZoomLevel(zoom);
    };

    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={Parser(pageName)}>
                <form onSubmit={handleSubmit}>
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
                                handleUserLevelZoom(
                                    mapRef.current?.getZoom() || 6
                                );
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
                                        <FireMarker className={"w-10 h-10"} />
                                    </Marker>
                                )}
                            </>
                        </Map>
                    </div>
                    <div className={`mt-4 space-x-4`}>
                        <PrimaryButton>Save</PrimaryButton>
                        <Link
                            className={"text-grey-200 inline-block text-center"}
                            href={route("my.events")}
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default EditEventPage;
