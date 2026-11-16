import { EyeSlashIcon } from "@heroicons/react/24/solid";
import { useForm } from "@inertiajs/react";

import { EventAdminActionModal } from "@/Components/Events/EventAdminCard/Modals/EventAdminActionModal";

export const EventUnpublishModal = ({
    open,
    setOpen,
    event,
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
    event: App.Data.EventData;
}) => {
    const form = useForm({});

    const unpublishEvent = () => {
        form.put(route("toggle-event-approved-at", [event]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <EventAdminActionModal
            open={open}
            setOpen={setOpen}
            icon={EyeSlashIcon}
            triggerLabel="Unpublish Event"
            heading={`Unpublish ${event.name}?`}
            confirmLabel="Unpublish"
            onConfirm={unpublishEvent}
            processing={form.processing}
        >
            Are you sure you want to unpublish this Event and make it invisible
            to the public?
        </EventAdminActionModal>
    );
};
