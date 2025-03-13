import React from "react";

import { Dialog } from "@headlessui/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { FireMarker } from "@/Components/Icons/FireMarker";
import { Modal } from "@/Components/Ui/Modal";
import { Spinner } from "@/Components/Ui/Spinner";
import { EventFormDataType } from "@/types/EventFormDataType";

interface FireConfirmationModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
    data: EventFormDataType;
    eventLocation: string;
    processing: boolean;
    onConfirm: () => void;
}

export const FireConfirmationModal: React.FC<FireConfirmationModalProps> = ({
    open,
    setOpen,
    data,
    eventLocation,
    processing,
    onConfirm,
}) => {
    return (
        <Modal open={open} setOpen={setOpen}>
            <div>
                <Dialog.Title
                    as="h3"
                    className="text-lg font-medium leading-6 text-white flex items-center"
                >
                    <FireMarker className="w-6 h-6 mr-2" />
                    Confirm Fire Details
                </Dialog.Title>
                <div className="mt-4 space-y-2">
                    <ul className="text-sm space-y-1 mt-3">
                        <li>
                            <span className="font-medium">Fire Name:</span>{" "}
                            {data.name} Fire
                        </li>
                        <li>
                            <span className="font-medium">Location:</span>{" "}
                            {eventLocation}
                        </li>
                        {data.country && (
                            <li>
                                <span className="font-medium">Country:</span>{" "}
                                {data.country}
                            </li>
                        )}
                        {data.region && (
                            <li>
                                <span className="font-medium">Region:</span>{" "}
                                {data.region}
                            </li>
                        )}
                    </ul>
                </div>
                <div className="mt-6 flex justify-between gap-3">
                    <SecondaryButton
                        type="button"
                        className="inline-flex items-center w-full justify-center rounded-md border border-gray-300 bg-transparent px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                        onClick={() => setOpen(false)}
                    >
                        Edit Details
                    </SecondaryButton>
                    <PrimaryButton
                        className="w-full"
                        onClick={onConfirm}
                        disabled={processing}
                    >
                        {processing ? <Spinner /> : "Confirm & Create Fire"}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    );
};
