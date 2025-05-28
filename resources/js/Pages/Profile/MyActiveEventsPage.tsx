import { usePage } from "@inertiajs/react";

import { EventAdminCard } from "@/Components/Events/EventAdminCard/EventAdminCard";
import { EventGrid } from "@/Components/Events/EventGrid";
import { MyEventsNav } from "@/Components/Events/MyEventsNav";
import { NoEvents } from "@/Components/Events/NoEvents";
import { HostNewEvent } from "@/Components/Profile/HostNewEvent";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const MyActiveEventsPage = () => {
    const pageTitle = `My Active Events`;
    const { events } = usePage().props;

    const activeEvents = (
        events as {
            data: App.Data.EventData[];
            meta: { links: any };
        }
    ).data;

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>

            <MyEventsNav />

            {activeEvents.length > 0 ? (
                <EventGrid className={`mt-8`}>
                    {activeEvents.map((event: App.Data.EventData) => (
                        <EventAdminCard event={event} key={event.id} />
                    ))}
                </EventGrid>
            ) : (
                <NoEvents type="active" />
            )}

            <HostNewEvent />
        </HostDashboardLayout>
    );
};

export default MyActiveEventsPage;
