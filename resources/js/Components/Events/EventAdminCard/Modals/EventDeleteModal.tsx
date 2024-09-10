import React from "react";

import { TrashIcon } from "@heroicons/react/24/outline";
import { XMarkIcon } from "@heroicons/react/24/solid";
import { useForm } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { SecondaryButton } from "@/Components/Buttons/SecondaryButton";
import { ModalHeading } from "@/Components/Type/ModalHeading";
import { Modal } from "@/Components/Ui/Modal";
import { ModalActions } from "@/Components/Ui/ModalActions";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import {
    eventAdminActionButtonClass,
    eventAdminActionButtonTextClass,
} from "@/helpers/eventAdminActionButtonClass";
import { Event } from "@/types/Event";

interface EventDeleteModalProps {
    open: boolean;
    setOpen: (open: boolean) => void;
    event: Event;
}

export const EventDeleteModal = ({
    open,
    setOpen,
    event,
}: EventDeleteModalProps) => {
    const form = useForm({});
    const deleteEvent = async () => {
        form.delete(route("delete.event", [event]), {
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
                className={eventAdminActionButtonClass}
                onClick={() => setOpen(true)}
            >
                <TrashIcon className={defaultIconSize} />
                <span className={eventAdminActionButtonTextClass}>
                    Delete Event
                </span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={"flex justify-end"}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon className={"w-6 h-6 text-white"} />
                    </button>
                </div>
                <ModalHeading>Delete Event?</ModalHeading>
                <p>Are you sure you want to delete this event?</p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton onClick={deleteEvent}>Delete</PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
