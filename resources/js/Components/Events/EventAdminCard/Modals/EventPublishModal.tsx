import { EyeIcon, XMarkIcon } from "@heroicons/react/24/solid";
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

export const EventPublishModal = ({
    open,
    setOpen,
    event,
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
    event: App.Data.EventData;
}) => {
    const form = useForm({});

    const publishEvent = () => {
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
                className={eventAdminActionButtonClass}
                onClick={() => setOpen(true)}
            >
                <EyeIcon className={defaultIconSize} />
                <span className={eventAdminActionButtonTextClass}>
                    Publish Event
                </span>
            </button>
            <Modal open={open} setOpen={setOpen}>
                <div className={"flex justify-end"}>
                    <button onClick={() => setOpen(false)}>
                        <XMarkIcon
                            className={`${defaultIconSize} text-white`}
                        />
                    </button>
                </div>
                <ModalHeading>Publish {event.name} Event?</ModalHeading>
                <p>
                    Are you sure you want to publish this Event and allow
                    contacts to make contact?
                </p>
                <ModalActions>
                    <SecondaryButton onClick={() => setOpen(false)}>
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        onClick={publishEvent}
                        disabled={form.processing}
                    >
                        Publish
                    </PrimaryButton>
                </ModalActions>
            </Modal>
        </>
    );
};
