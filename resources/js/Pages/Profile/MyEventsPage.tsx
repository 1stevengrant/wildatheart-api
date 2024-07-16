import React, { useState } from "react";

import { Tab } from "@headlessui/react";
import { usePage } from "@inertiajs/react";

import { EventAdminCard } from "@/Components/Events/EventAdminCard/EventAdminCard";
import { NoEvents } from "@/Components/Events/NoEvents";
import { HostNewEvent } from "@/Components/Profile/HostNewEvent";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { Event } from "@/types/Event";

const EventGrid = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div className={`grid gap-6 md:grid-cols-2 clear-both ${className}`}>
            {children}
        </div>
    );
};

const MyEventsPage = () => {
    const pageTitle = `My Events`;
    const { events } = usePage().props;

    const typedEvents = (
        events as {
            data: Event[];
            meta: { links: any };
        }
    ).data;

    const tabs = ["Active", "Inactive"];

    const [selectedTab, setSelectedTab] = useState(0);

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>

            <Tab.Group selectedIndex={selectedTab} onChange={setSelectedTab}>
                <Tab.List
                    className={`-mb-px flex space-x-8 border-b border-grey-darker`}
                >
                    {tabs.map((tab, index) => (
                        <Tab
                            className={`border-transparent text-white hover:border-orange hover:text-orange whitespace-nowrap border-b-2 py-4 px-1 focus:border-transparent focus:outline-none focus:ring-0 ui-selected:border-white ui-selected:font-bold`}
                            key={index}
                        >
                            {tab}
                        </Tab>
                    ))}
                </Tab.List>
                <Tab.Panels className={`py-6`}>
                    <Tab.Panel>
                        {typedEvents.length > 0 ? (
                            <EventGrid className={`mt-4`}>
                                {typedEvents
                                    .filter(
                                        (event: Event) =>
                                            !event.start_date ||
                                            new Date(event.start_date) >
                                                new Date()
                                    )
                                    .map((event: Event, index: number) => (
                                        <EventAdminCard
                                            event={event}
                                            key={index}
                                        />
                                    ))}
                            </EventGrid>
                        ) : (
                            <NoEvents type="active" />
                        )}

                        <HostNewEvent />
                    </Tab.Panel>
                    <Tab.Panel>
                        {typedEvents.length > 0 ? (
                            <EventGrid>
                                {typedEvents
                                    .filter(
                                        (event: Event) =>
                                            event.start_date &&
                                            new Date(event.start_date) <
                                                new Date()
                                    )
                                    .map((event: Event, index: number) => (
                                        <EventAdminCard
                                            event={event}
                                            key={index}
                                        />
                                    ))}
                            </EventGrid>
                        ) : (
                            <NoEvents type="inactive" />
                        )}

                        <HostNewEvent />
                    </Tab.Panel>
                </Tab.Panels>
            </Tab.Group>
        </HostDashboardLayout>
    );
};

export default MyEventsPage;
