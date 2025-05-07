import { usePage } from "@inertiajs/react";

import { EventAdminCard } from "@/Components/Events/EventAdminCard/EventAdminCard";
import { EventGrid } from "@/Components/Events/EventGrid";
import { MyEventsNav } from "@/Components/Events/MyEventsNav";
import { NoEvents } from "@/Components/Events/NoEvents";
import { HostNewEvent } from "@/Components/Profile/HostNewEvent";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const MyInactiveEventsPage = () => {
    const pageTitle = `My Inactive Events`;
    const { events } = usePage().props;

    const inactiveEvents = (
        events as {
            data: App.Data.EventData[];
            meta: { links: any };
        }
    ).data;

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>

            <MyEventsNav />

            {inactiveEvents.length > 0 ? (
                <EventGrid className={`mt-8`}>
                    {inactiveEvents.map((event: App.Data.EventData) => (
                        <EventAdminCard event={event} key={event.id} />
                    ))}
                </EventGrid>
            ) : (
                <NoEvents type="inactive" />
            )}
            <HostNewEvent />
        </HostDashboardLayout>
    );
};

export default MyInactiveEventsPage;
