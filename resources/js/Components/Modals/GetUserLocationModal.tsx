import React, { useState, useEffect } from "react";

import { XMarkIcon } from "@heroicons/react/24/solid";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { StaticMap } from "@/Components/Maps/StaticMap";
import { ModalHeading } from "@/Components/Type/ModalHeading";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { Spinner } from "@/Components/Ui/Spinner";
import { defaultLatLng } from "@/helpers/defaultLatLng";
import { useMapData } from "@/Hooks/useMapData";
import {
    setUserLocation,
    setModalDismissed,
    shouldShowModal,
    getUserLocation,
} from "@/lib/localStorage";

export const GetUserLocationModal = () => {
    const { setLatitude, setLongitude } = useMapData();
    const [open, setOpen] = useState(false);
    const [gettingLocation, setGettingLocation] = useState(false);
    const [positionError, setPositionError] =
        useState<GeolocationPositionError | null>(null);

    useEffect(() => {
        const shouldShow = shouldShowModal();
        console.log("Should show modal:", shouldShow);
        setOpen(shouldShow);
    }, []);

    const handleModalClose = () => {
        console.log("Modal closed");
        setModalDismissed();
        setToDefaultLocation();
        setOpen(false);
    };

    const setToDefaultLocation = () => {
        console.log("Setting to default location");
        setLatitude(defaultLatLng.latitude);
        setLongitude(defaultLatLng.longitude);
        setUserLocation(defaultLatLng.latitude, defaultLatLng.longitude);
    };

    const handleUseMyLocation = () => {
        setGettingLocation(true);

        const positionSuccess = (position: GeolocationPosition) => {
            console.log("Geolocation success:", position);
            const { latitude, longitude } = position.coords;

            setLatitude(latitude);
            setLongitude(longitude);
            setUserLocation(latitude, longitude);
            setModalDismissed();
            setOpen(false);

            // Verify that the location was saved
            const savedLocation = getUserLocation();
            console.log("Saved location after setting:", savedLocation);
        };
        const positionError = (error: GeolocationPositionError) => {
            console.log("Geolocation error:", error);
            setPositionError(error);

            setTimeout(() => {
                handleModalClose();
            }, 5000);
        };

        navigator.geolocation.getCurrentPosition(
            positionSuccess,
            positionError
        );
    };

    return (
        <Modal open={open} setOpen={setOpen}>
            <div className={"flex justify-end"}>
                <button onClick={handleModalClose}>
                    <XMarkIcon className={"w-6 h-6 text-white"} />
                </button>
            </div>
            <ModalHeading>Use Your Location?</ModalHeading>

            {!gettingLocation && (
                <p>
                    We'd love to use your location to show you events that are
                    happening near you.
                </p>
            )}

            {positionError && (
                <p className={`text-center text-red-dark`}>
                    We were unable to get your location. Please try again.
                </p>
            )}

            {gettingLocation && (
                <div className={`text-center`}>
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
                <SecondaryButton onClick={handleModalClose}>
                    No thanks
                </SecondaryButton>
                <PrimaryButton onClick={handleUseMyLocation}>
                    Use My Location
                </PrimaryButton>
            </ModalActions>
        </Modal>
    );
};
