import { useEffect } from "react";

import { usePage } from "@inertiajs/react";
import Vimeo from "@u-wave/react-vimeo";
import Markdown from "react-markdown";

import { BackToEvents } from "@/Components/Buttons/BackToEvents";
import { ConnectWithHost } from "@/Components/Events/Detail/ConnectWithHost";
import { Date } from "@/Components/Events/Detail/Dates";
import { Host } from "@/Components/Events/Detail/Host";
import { KeyDetails } from "@/Components/Events/Detail/KeyDetails";
import { Location } from "@/Components/Events/Detail/Location";
import { MobileConnect } from "@/Components/Events/Detail/MobileConnect";
import { PhotoCarousel } from "@/Components/Events/Detail/PhotoCarousel";
import { ShareEvent } from "@/Components/Events/Detail/ShareEvent";
import { UnpublishedBadge } from "@/Components/Events/Detail/UnpublishedBadge";
import { StaticMap } from "@/Components/Maps/StaticMap";
import { dateFormatter } from "@/helpers/dateFormatter";
import {
    EventTypeBecomingAKingExperience,
    EventTypeCaptivatingExperience,
    EventTypeCaptivatingFires,
    EventTypeFires,
    EventTypeWahExperience,
} from "@/helpers/eventNames";
import { proseClasses } from "@/helpers/proseClasses";
import { AppLayout } from "@/Layouts/AppLayout";
import { Event } from "@/types/Event";

const ViewEventPage = () => {
    const page = usePage();
    const event = page.props.event as Event;

    useEffect(() => {
        if (window.innerWidth < 768) {
            const eventElement = document.getElementById("event"),
                connectWithHostElement = document.getElementById(
                    "mobile-connect-with-host"
                );

            if (!eventElement || !connectWithHostElement) return;

            eventElement.style.paddingBottom =
                connectWithHostElement.offsetHeight + 16 + "px";
        }
    });

    const eventTitle = (event: Event) => {
        switch (event.eventType) {
            case EventTypeCaptivatingFires:
                return `${event.name} Captivating Fire`;
            case EventTypeFires:
                return `${event.name} Fire`;
            default:
                return event.eventTypeData.name;
        }
    };

    const eventDate = (event: Event): string => {
        if (
            event.eventType === EventTypeCaptivatingFires ||
            event.eventType === EventTypeFires
        ) {
            return "";
        }

        if (
            [
                EventTypeCaptivatingExperience,
                EventTypeWahExperience,
                EventTypeBecomingAKingExperience,
            ].includes(event.eventType)
        ) {
            return `Starts on ${dateFormatter(event.startDate)}`;
        }

        return `${dateFormatter(event.startDate)} - ${dateFormatter(event.endDate)}`;
    };

    return (
        <AppLayout
            title={`${event.name} hosted by ${event.host} in ${event.location}`}
            showFooter={false}
        >
            <div className={"relative"}>
                <StaticMap
                    lng={event.longitude}
                    lat={event.latitude}
                    event={event}
                />
                <BackToEvents />
                <ShareEvent />
            </div>
            <div
                className="relative border-t bg-black p-6 text-white top-[-32px] rounded-t-[2.5rem] border-grey-darker"
                id={"event"}
            >
                <div className="container mx-auto">
                    <div className="md:space-x-8 md:flex">
                        <div className="md:w-2/3">
                            <div className="flex items-center space-x-4">
                                <h1 className="mb-1 flex items-center text-3xl leading-tighter">
                                    {eventTitle(event)}
                                    {!event.approved_at && <UnpublishedBadge />}
                                </h1>
                            </div>

                            <div className="mt-4 space-y-4 md:space-x-4 md:space-y-0 md:flex md:items-start">
                                <Location location={event.location} />
                                {eventDate(event) && (
                                    <Date date={eventDate(event)} />
                                )}
                            </div>

                            {event.eventTypeData.hero_video && (
                                <Vimeo
                                    responsive={true}
                                    className={`mt-[48px]`}
                                    video={event.eventTypeData.hero_video}
                                    autoplay
                                />
                            )}

                            {event.eventTypeData.hero_image && (
                                <img
                                    src={event.eventTypeData.hero_image}
                                    className={`mt-[48px]`}
                                    alt={``}
                                />
                            )}
                            <KeyDetails event={event} />
                            <Markdown className={proseClasses}>
                                {event.eventTypeData.description}
                            </Markdown>

                            {event.eventTypeData.photos &&
                                event.eventTypeData.photos.length > 0 && (
                                    <PhotoCarousel
                                        className={`mt-8`}
                                        images={event.eventTypeData.photos}
                                    />
                                )}
                        </div>

                        <div className="relative hidden md:block md:w-1/3">
                            <Host
                                host={event.host}
                                hostPhoto={event.hostPhoto}
                            />

                            <ConnectWithHost className={"mt-6 sticky top-4"} />
                        </div>
                    </div>
                </div>
            </div>

            <MobileConnect />
        </AppLayout>
    );
};

export default ViewEventPage;
