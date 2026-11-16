import { LockClosedIcon, LockOpenIcon } from "@heroicons/react/24/outline";
import { useForm } from "@inertiajs/react";

import { EventAdminActionModal } from "@/Components/Events/EventAdminCard/Modals/EventAdminActionModal";

export const EventPublicPrivateModal = ({
    open,
    setOpen,
    event,
}: {
    event: App.Data.EventData;
    open: boolean;
    setOpen: (open: boolean) => void;
}) => {
    const form = useForm({});

    const visibility = event.is_private ? "Public" : "Private";

    const toggleEventPrivacy = () => {
        form.put(route("toggle-event-privacy", [event]), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <EventAdminActionModal
            open={open}
            setOpen={setOpen}
            icon={event.is_private ? LockOpenIcon : LockClosedIcon}
            triggerLabel={`Make ${visibility}`}
            heading={`Make ${event.name} ${visibility}`}
            confirmLabel={`Make ${visibility}`}
            onConfirm={toggleEventPrivacy}
            processing={form.processing}
        >
            Are you sure you want to make this event {visibility.toLowerCase()}?
        </EventAdminActionModal>
    );
};
