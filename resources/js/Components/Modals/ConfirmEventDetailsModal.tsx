import React from "react";

import { Dialog } from "@headlessui/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { Modal } from "@/Components/Ui/Modal";
import { Spinner } from "@/Components/Ui/Spinner";
import { EventFormDataType } from "@/types/EventFormDataType";

interface EventConfirmationModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
    data: EventFormDataType;
    eventLocation: string;
    processing: boolean;
    onConfirm: () => void;
}

export const ConfirmEventDetailsModal: React.FC<
    EventConfirmationModalProps
> = ({ open, setOpen, data, eventLocation, processing, onConfirm }) => {
    return (
        <Modal open={open} setOpen={setOpen}>
            <div>
                <Dialog.Title
                    as="h3"
                    className="text-lg font-medium leading-6 text-white"
                >
                    Confirm Event Details
                </Dialog.Title>
                <div className="mt-4 space-y-2">
                    <p>Please confirm your event details:</p>
                    <ul className="text-sm space-y-1">
                        <li>
                            <span className="font-medium">Location:</span>{" "}
                            {eventLocation}
                        </li>
                        {data.start_date && (
                            <li>
                                <span className="font-medium">Start Date:</span>{" "}
                                {new Date(data.start_date).toLocaleDateString()}
                            </li>
                        )}
                        <li>
                            <span className="font-medium">Visibility:</span>{" "}
                            {data.is_private ? "Private" : "Public"}
                        </li>
                        {data.number_of_items_in_material_order && (
                            <li>
                                <span className="font-medium">
                                    Materials Ordered:
                                </span>{" "}
                                {data.number_of_items_in_material_order}
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
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        className="w-full"
                        onClick={onConfirm}
                        disabled={processing}
                    >
                        {processing ? <Spinner /> : "Confirm & Create"}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    );
};
