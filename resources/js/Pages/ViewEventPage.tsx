import React from "react";

import { BackToEvents } from "@/Components/Buttons/BackToEvents";
import { EventContent } from "@/Components/Events/Detail/EventContent";
import { EventHeader } from "@/Components/Events/Detail/EventHeader";
import { EventMedia } from "@/Components/Events/Detail/EventMedia";
import { MobileConnect } from "@/Components/Events/Detail/MobileConnect";
import { ShareEvent } from "@/Components/Events/Detail/ShareEvent";
import { SidebarContent } from "@/Components/Events/Detail/SidebarContent";
import { StaticMap } from "@/Components/Maps/StaticMap";
import { useMobileLayout } from "@/Hooks/useMobileLayout";
import { AppLayout } from "@/Layouts/AppLayout";

export default function ViewEventPage({
    event,
}: {
    event: App.Data.EventData;
}) {
    useMobileLayout();
    return (
        <AppLayout
            title={`${event.name} hosted by ${event.host} in ${event.location}`}
            showFooter={false}
        >
            <div className="relative">
                <StaticMap
                    lng={event.longitude}
                    lat={event.latitude}
                    event={event}
                />
                <BackToEvents />
                <ShareEvent />
            </div>

            <div
                className="relative border-t bg-black p-6 text-white top-[-32px] rounded-t-[2.5rem] border-n400"
                id="event"
            >
                <div className="container mx-auto">
                    <div className="md:space-x-8 md:flex">
                        <div className="md:w-2/3">
                            <EventHeader event={event} />
                            <EventMedia event={event} />
                            <EventContent event={event} />
                        </div>

                        <SidebarContent event={event} />
                    </div>
                </div>
            </div>
            <MobileConnect />
        </AppLayout>
    );
}
