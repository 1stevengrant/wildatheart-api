import React, { useEffect } from "react";

import { EditFireForm } from "@/Components/Events/EditFireForm";
import { EditScheduledEventForm } from "@/Components/Events/EditScheduledEventForm";
import { EventHosts } from "@/Components/Events/EventHosts";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const EditEventPage = () => {
    const page = useTypedPage();

    const user = page.props.auth.user!;
    const event = page.props.event as App.Data.EventData;
    const managers = page.props.managers as App.Data.EventManagerData[];
    const pendingTransfer = page.props
        .pendingTransfer as App.Data.EventOwnershipTransferData | null;
    const isOwner = page.props.isOwner as boolean;
    const pageTitle = `Editing ${event.name}`;
    const isFire = event.type_group === fireTypeGroup;

    useEffect(() => {
        // Handle hash-based scrolling for Inertia navigation
        const hash = window.location.hash;
        if (hash) {
            const element = document.querySelector(hash);
            if (element) {
                element.scrollIntoView({ behavior: "smooth" });
            }
        }
    }, []);

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>
                {isFire
                    ? `Editing ${event.name}`
                    : `Editing ${event.event_type_data.name}`}
            </HeadingOne>

            {isFire ? (
                <EditFireForm event={event} user={user} />
            ) : (
                <EditScheduledEventForm event={event} user={user} />
            )}

            <div className="mt-8">
                <EventHosts
                    eventId={event.id}
                    managers={managers}
                    pendingTransfer={pendingTransfer}
                    isOwner={isOwner}
                />
            </div>
        </HostDashboardLayout>
    );
};

export default EditEventPage;
