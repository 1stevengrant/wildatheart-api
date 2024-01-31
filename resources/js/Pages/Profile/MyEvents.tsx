import React from "react";
import AppLayout from "@/Layouts/AppLayout";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import {Link, usePage} from "@inertiajs/react";
import {Event} from "@/types/Event";
import EventAdminCard from "@/Components/Events/EventAdminCard";
import type { PageProps} from '@inertiajs/core'
import primaryButtonClasses from "@/helpers/primaryButtonClasses";

const MyEvents = () => {
    const pageTitle = `My Events`;
    // @ts-ignore
    const {events: {data: events}} = usePage().props as PageProps;

    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>

                {events.length > 0 ? (
                    <div className={`grid gap-6 md:grid-cols-2 clear-both`}>
                        {events.map((event: Event, index: number) => (
                            <EventAdminCard event={event} key={index}/>
                        ))}
                    </div>
                ) : (
                    <p className="clear-both text-center text-2xl">
                        You have no Fires currently
                    </p>
                )}

                <div className="mt-6 flex items-center justify-center">
                    <Link href={route('host')}
                          className={`${primaryButtonClasses}`}>
                        Start a New Fire
                    </Link>
                </div>

            </MyProfileWrapper>
        </AppLayout>
    )
}

export default MyEvents;
