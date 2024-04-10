import React from "react";

import { EyeSlashIcon, XMarkIcon } from "@heroicons/react/24/solid";
import { useForm } from "@inertiajs/react";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import SecondaryButton from "@/Components/Buttons/SecondaryButton";
import ModalHeading from "@/Components/Type/ModalHeading";
import Modal from "@/Components/Ui/Modal";
import ModalActions from "@/Components/Ui/ModalActions";
import { Event } from "@/types/Event";

interface EventUnpublishModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
    event: Event;
}

const EventUnpublishModal = ({
    open,
    setOpen,
    event,
}: EventUnpublishModalProps) => {
    const form = useForm({
        approved_at: null,
    });

    const unpublishEvent = async () => {
        form.put(route("toggle-event-approved-at", [event]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                setOpen(false);
            },
        });
    };

    return (
        <>
            <button
                className="flex items-center space-x-2 hover:text-orange"
                onClick={() => setOpen(true)}
            >
                <EyeSlashIcon className="h-6 w-6" />
                <span className="hidden md:inline">Unpublish Event</span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={"flex justify-end"}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={"w-6 h-6 text-white"} />
                    </button>
                </div>
                <ModalHeading>Unpublish {event.name}?</ModalHeading>
                <p>
                    Are you sure you want to unpublish this Event and make it
                    invisible to the public?
                </p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton onClick={unpublishEvent}>
                        Unpublish
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};

export default EventUnpublishModal;
