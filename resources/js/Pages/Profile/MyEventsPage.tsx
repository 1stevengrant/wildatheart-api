import React from "react";

import { Link, usePage } from "@inertiajs/react";

import { EventAdminCard } from "@/Components/Events/EventAdminCard";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { AppLayout } from "@/Layouts/AppLayout";
import { MyProfileWrapper } from "@/Components/Layout/MyProfileWrapper";
import { Event } from "@/types/Event";

const MyEventsPage = () => {
    const pageTitle = `My Events`;
    const { events } = usePage().props;

    const typedEvents = (
        events as {
            data: Event[];
            meta: { links: any };
        }
    ).data;

    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                {typedEvents.length > 0 ? (
                    <div className={`grid gap-6 md:grid-cols-2 clear-both`}>
                        {typedEvents.map((event: Event, index: number) => (
                            <EventAdminCard event={event} key={index} />
                        ))}
                    </div>
                ) : (
                    <p className="clear-both text-center text-2xl">
                        You have no Fires currently
                    </p>
                )}

                <div className="mt-6 flex items-center justify-center">
                    <Link
                        href={route("host")}
                        className={`${primaryButtonClasses}`}
                    >
                        Host a New Event
                    </Link>
                </div>
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default MyEventsPage;
