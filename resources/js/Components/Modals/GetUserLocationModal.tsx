import React from "react";

import { MapPinIcon } from "@heroicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/solid";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { StaticMap } from "@/Components/Maps/StaticMap";
import { ModalHeading } from "@/Components/Type/ModalHeading";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { defaultLatLng } from "@/helpers/defaultLatLng";
import { useMapData } from "@/Hooks/useMapData";
import { mapFlyTo } from "@/lib/mapFlyTo";

interface GetUserLocationModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
}

export const GetUserLocationModal = ({
    open,
    setOpen,
}: GetUserLocationModalProps) => {
    const handleModalClose = () => {
        setOpen(false);
    };
    const { mapRef } = useMapData();

    const { setLatitude, setLongitude } = useMapData();

    const getMyLocation = () => {
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            setLatitude(latitude);
            setLongitude(longitude);
            handleModalClose();
        });
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
                    <PrimaryButton onClick={getMyLocation}>
                        Use My Location
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
