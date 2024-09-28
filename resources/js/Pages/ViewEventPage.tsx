import React, { useEffect } from "react";

import { usePage } from "@inertiajs/react";
import Vimeo from "@u-wave/react-vimeo";
import Markdown from "react-markdown";

import { BackToEvents } from "@/Components/Buttons/BackToEvents";
import { ConnectWithHost } from "@/Components/Events/Detail/ConnectWithHost";
import { Date } from "@/Components/Events/Detail/Dates";
import { Host } from "@/Components/Events/Detail/Host";
import { Location } from "@/Components/Events/Detail/Location";
import { MobileConnect } from "@/Components/Events/Detail/MobileConnect";
import { PhotoCarousel } from "@/Components/Events/Detail/PhotoCarousel";
import { ShareEvent } from "@/Components/Events/Detail/ShareEvent";
import { StaticMap } from "@/Components/Maps/StaticMap";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { dateFormatter } from "@/helpers/dateFormatter";
import {
    EventTypeBecomingAKingExperience,
    EventTypeCaptivatingExperience,
    EventTypeFires,
    EventTypeWahExperience,
} from "@/helpers/eventNames";
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
        console.log(event);
        switch (event.eventType) {
            case EventTypeFires:
                return `${event.name} Fire`;
            default:
                return event.eventTypeData.name;
        }
    };

    const eventDate = (event: Event): string => {
        switch (event.eventType) {
            case EventTypeFires:
                return "";
            case EventTypeCaptivatingExperience:
            case EventTypeWahExperience:
            case EventTypeBecomingAKingExperience:
                return `Starts on ${dateFormatter(event.startDate)}`;
            default:
                return `${dateFormatter(event.startDate)} - ${dateFormatter(event.endDate)}`;
        }
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
                <ShareEvent
                    className={`shadow-xl hover:shadow-none transition absolute top-[20px] right-4 bg-white z-10 flex items-center space-x-2 rounded-full px-4 py-2 text-black font-medium hover:text-orange duration-200 ease-in-out`}
                />
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
                                    {!event.approved_at && (
                                        <span className="ml-2 rounded px-2 py-1 text-sm font-semibold uppercase text-white bg-orange">
                                            Unpublished
                                        </span>
                                    )}
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

                            {event.eventTypeData.key_details &&
                                event.eventTypeData.key_details.length > 0 && (
                                    <div>
                                        <HeadingOne className={`mt-[48px]`}>
                                            Key Details
                                        </HeadingOne>
                                        <ul className={`my-8`}>
                                            {event.eventTypeData.key_details.map(
                                                (item: any, index: number) => (
                                                    <li
                                                        className="mb-4 flex items-start"
                                                        key={index}
                                                    >
                                                        <div
                                                            dangerouslySetInnerHTML={{
                                                                __html: item
                                                                    .fields
                                                                    .icon,
                                                            }}
                                                            className={`w-6 h-6`}
                                                        />
                                                        <div className="ml-[24px] leading-[24px]">
                                                            <p className="font-medium">
                                                                {
                                                                    item.fields
                                                                        .headline
                                                                }
                                                            </p>
                                                            <p className="text-sm text-[#B8B8B8]">
                                                                {
                                                                    item.fields
                                                                        .description
                                                                }
                                                            </p>
                                                        </div>
                                                    </li>
                                                )
                                            )}
                                        </ul>
                                    </div>
                                )}

                            <div
                                className={`mt-4 prose prose-p:text-white prose-a:text-orange max-w-none prose-hr:bg-black prose-h2:text-white prose-h3:text-white  prose-li:text-white`}
                            >
                                <Markdown>
                                    {event.eventTypeData.description}
                                </Markdown>
                            </div>

                            {event.eventTypeData.photos &&
                                event.eventTypeData.photos.length > 0 && (
                                    <PhotoCarousel
                                        className={`mt-8`}
                                        images={event?.eventTypeData.photos}
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
