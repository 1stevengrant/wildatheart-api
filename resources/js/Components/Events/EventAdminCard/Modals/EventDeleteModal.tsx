import { TrashIcon } from "@heroicons/react/24/outline";
import { useForm } from "@inertiajs/react";

import { EventAdminActionModal } from "@/Components/Events/EventAdminCard/Modals/EventAdminActionModal";

export const EventDeleteModal = ({
    open,
    setOpen,
    event,
}: {
    open: boolean;
    setOpen: (open: boolean) => void;
    event: App.Data.EventData;
}) => {
    const form = useForm({});

    const deleteEvent = () => {
        form.delete(route("delete.event", [event]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <EventAdminActionModal
            open={open}
            setOpen={setOpen}
            icon={TrashIcon}
            triggerLabel="Delete Event"
            heading="Delete Event?"
            confirmLabel="Delete"
            onConfirm={deleteEvent}
            processing={form.processing}
        >
            Are you sure you want to delete this event?
        </EventAdminActionModal>
    );
};
