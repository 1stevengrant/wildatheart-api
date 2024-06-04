import React, { useState } from "react";

import { Tab, TabGroup, TabList, TabPanel, TabPanels } from "@headlessui/react";
import { usePage } from "@inertiajs/react";

import { EventAdminCard } from "@/Components/Events/EventAdminCard";
import { HostNewEvent } from "@/Components/Profile/HostNewEvent";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { Event } from "@/types/Event";

const EventGrid = ({ children }: { children: React.ReactNode }) => {
    return (
        <div className={`grid gap-6 md:grid-cols-2 clear-both`}>{children}</div>
    );
};

const NoEvents = ({ type }: { type: string }) => {
    return (
        <p className="clear-both text-center text-2xl">
            You have no {type} events currently
        </p>
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

            <TabGroup selectedIndex={selectedTab} onChange={setSelectedTab}>
                <TabList
                    className={`-mb-px flex space-x-8 border-b border-grey-darker`}
                >
                    {tabs.map((tab, index) => (
                        <Tab
                            className={`border-transparent text-white hover:border-orange hover:text-orange whitespace-nowrap border-b-2 py-4 px-1 focus:border-transparent focus:outline-none focus:ring-0 data-[selected]:border-white data-[selected]:font-bold`}
                            key={index}
                        >
                            {tab}
                        </Tab>
                    ))}
                </TabList>
                <TabPanels className={`py-6`}>
                    <TabPanel>
                        {typedEvents.length > 0 ? (
                            <EventGrid>
                                {typedEvents
                                    .filter((event: Event) => event.approved_at)
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
                    </TabPanel>
                    <TabPanel>
                        {typedEvents.length > 0 ? (
                            <EventGrid>
                                {typedEvents
                                    .filter(
                                        (event: Event) => !event.approved_at
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
                    </TabPanel>
                </TabPanels>
            </TabGroup>
        </HostDashboardLayout>
    );
};

export default MyEventsPage;
