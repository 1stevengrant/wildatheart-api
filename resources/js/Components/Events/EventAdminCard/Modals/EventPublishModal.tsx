import { EyeIcon } from "@heroicons/react/24/solid";
import { useForm } from "@inertiajs/react";

import { EventAdminActionModal } from "@/Components/Events/EventAdminCard/Modals/EventAdminActionModal";

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
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <EventAdminActionModal
            open={open}
            setOpen={setOpen}
            icon={EyeIcon}
            triggerLabel="Publish Event"
            heading={`Publish ${event.name} Event?`}
            confirmLabel="Publish"
            onConfirm={publishEvent}
            processing={form.processing}
        >
            Are you sure you want to publish this Event and allow contacts to
            make contact?
        </EventAdminActionModal>
    );
};
