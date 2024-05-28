import React, { useState } from "react";

import { MapPinIcon } from "@heroicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/solid";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { StaticMap } from "@/Components/Maps/StaticMap";
import ModalHeading from "@/Components/Type/ModalHeading";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";
import Spinner from "@/Components/Ui/Spinner";
import { defaultLatLng } from "@/helpers/defaultLatLng";
import { useMapData } from "@/Hooks/useMapData";

interface GetUserLocationModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
    onLocationSet: (latitude: number, longitude: number) => void;
}

export const GetUserLocationModal = ({
    open,
    setOpen,
    onLocationSet,
}: GetUserLocationModalProps) => {
    const { setLatitude, setLongitude } = useMapData();
    const [gettingLocation, setGettingLocation] = useState(false);

    const rejectLocationPermissions = () => {
        localStorage.setItem("locationPermissions", "denied");
        setLatitude(defaultLatLng.latitude);
        setLongitude(defaultLatLng.longitude);
        onLocationSet(defaultLatLng.latitude, defaultLatLng.longitude);
        console.log("Location Permissions Denied, get default events");
        setOpen(false);
    };

    const useMyLocation = () => {
        setGettingLocation(true);
        localStorage.setItem("locationPermissions", "granted");
        navigator.geolocation.getCurrentPosition(
            successCallback,
            errorCallback
        );
    };

    const handleModalClose = () => {
        setLatitude(defaultLatLng.latitude);
        setLongitude(defaultLatLng.longitude);
        onLocationSet(defaultLatLng.latitude, defaultLatLng.longitude);
        console.log("Location modal closed but not denied, get default events");
        setOpen(false);
    };

    const successCallback = (position: GeolocationPosition) => {
        setGettingLocation(false);
        console.log("Latitude:", position.coords.latitude);
        console.log("Longitude:", position.coords.longitude);
        setLatitude(position.coords.latitude);
        setLongitude(position.coords.longitude);
        localStorage.setItem("latitude", position.coords.latitude.toString());
        localStorage.setItem("longitude", position.coords.longitude.toString());
        onLocationSet(position.coords.latitude, position.coords.longitude);
        setOpen(false);
    };

    const errorCallback = (error: GeolocationPositionError) => {
        console.error("Error getting location:", error.message);
        setLatitude(defaultLatLng.latitude);
        setLongitude(defaultLatLng.longitude);
        setOpen(false);
    };

    return (
        <>
            <button
                className="flex items-center space-x-2 hover:text-orange"
                onClick={() => setOpen(true)}
            >
                <MapPinIcon className="h-6 w-6" />
                <span className="hidden md:inline">Use Your Location?</span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={"flex justify-end"}>
                    <button onClick={handleModalClose}>
                        <XMarkIcon className={"w-6 h-6 text-white"} />
                    </button>
                </div>
                <ModalHeading>Use Your Location?</ModalHeading>

                <p>
                    We'd love to use your location to show you events that are
                    happening near you.
                </p>

                {gettingLocation && (
                    <div className={`text-center mt-6`}>
                        <p>Getting your location...</p>
                        <Spinner className={`w-6 inline-block mx-auto`} />
                    </div>
                )}

                <StaticMap
                    className={`rounded-lg mt-4`}
                    lat={defaultLatLng.latitude}
                    lng={defaultLatLng.longitude}
                    event={null}
                />

                <ModalActions>
                    <SecondaryButton onClick={rejectLocationPermissions}>
                        No thanks
                    </SecondaryButton>
                    <PrimaryButton onClick={useMyLocation}>
                        Use My Location
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
