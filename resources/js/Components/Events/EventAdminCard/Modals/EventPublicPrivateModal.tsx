import React from "react";

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
        is_private: event.isPrivate,
    });

    const toggleEventPrivacy = async () => {
        form.setData("is_private", !form.data.is_private);
        form.put(route("toggle-event-privacy", [event]), {
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
                {event.isPrivate ? (
                    <>
                        <LockOpenIcon className={defaultIconSize} />
                        <span className={eventAdminActionButtonTextClass}>
                            Make Public
                        </span>
                    </>
                ) : (
                    <>
                        <LockClosedIcon className={defaultIconSize} />
                        <span className={eventAdminActionButtonTextClass}>
                            Make Private
                        </span>
                    </>
                )}
            </button>

            <Modal open={open} setOpen={setOpen}>
                <div className={"flex justify-end"}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon
                            className={`${defaultIconSize} text-white`}
                        />
                    </button>
                </div>
                <ModalHeading>
                    Make {event.name} {event.isPrivate ? `Public` : `Private`}
                </ModalHeading>
                <p>
                    Are you sure you want to make this event{" "}
                    {event.isPrivate ? `public` : `private`}?
                </p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton onClick={() => toggleEventPrivacy()}>
                        {event.isPrivate ? `Make Public` : `Make Private`}
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
