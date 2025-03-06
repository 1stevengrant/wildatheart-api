import React, { useMemo } from "react";

import { LockClosedIcon, LockOpenIcon } from "@heroicons/react/24/outline";
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

export const EventPublicPrivateModal = ({
    open,
    setOpen,
    event,
}: {
    event: Event;
    open: boolean;
    setOpen: (open: boolean) => void;
}) => {
    const form = useForm({
        is_private: event.is_private,
    });

    // Memoize toggle-related content to avoid recalculation on rerenders
    const toggleContent = useMemo(() => {
        return {
            buttonText: event.is_private ? "Make Public" : "Make Private",
            modalText: `Make ${event.name} ${event.is_private ? "Public" : "Private"}`,
            confirmationText: `Are you sure you want to make this event ${event.is_private ? "public" : "private"}?`,
            icon: event.is_private ? LockOpenIcon : LockClosedIcon,
        };
    }, [event.is_private, event.name]);

    const toggleEventPrivacy = async () => {
        form.setData("is_private", !form.data.is_private);
        form.put(route("toggle-event-privacy", [event]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => setOpen(false),
        });
    };

    const handleCloseModal = () => setOpen(false);

    // Dynamically determine which icon to use
    const Icon = toggleContent.icon;

    return (
        <>
            <button
                className={eventAdminActionButtonClass}
                onClick={() => setOpen(true)}
                aria-label={toggleContent.buttonText}
            >
                <Icon className={defaultIconSize} />
                <span className={eventAdminActionButtonTextClass}>
                    {toggleContent.buttonText}
                </span>
            </button>

            <Modal open={open} setOpen={setOpen}>
                <div className="flex justify-end">
                    <button onClick={handleCloseModal} aria-label="Close modal">
                        <XMarkIcon
                            className={`${defaultIconSize} text-white`}
                        />
                    </button>
                </div>
                <ModalHeading>{toggleContent.modalText}</ModalHeading>
                <p>{toggleContent.confirmationText}</p>
                <ModalActions>
                    <SecondaryButton onClick={handleCloseModal}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        onClick={toggleEventPrivacy}
                        disabled={form.processing}
                    >
                        {toggleContent.buttonText}
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
