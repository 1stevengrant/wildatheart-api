import React, {useEffect} from "react";
import AppLayout from "../Layouts/AppLayout";
import StaticMap from "@/Components/Maps/StaticMap";
import {usePage} from "@inertiajs/react";
import {Event} from "@/types/Event";
import BackToEvents from "@/Components/Buttons/BackToEvents";
import ConnectWithHost from "@/Components/Events/ConnectWithHost";
import UpdateAddress from "@/Components/Events/UpdateAddress";
import MobileConnect from "@/Components/Events/MobileConnect";
import {PageProps} from "@/types";
import Host from "@/Components/Events/Detail/Host";
import Location from "@/Components/Events/Detail/Location";
import Date from "@/Components/Events/Detail/Dates";
import VideoPlayer from "@/Components/Events/Detail/VideoPlayer";
import HeadingOne from "@/Components/Type/HeadingOne";
import Markdown from "react-markdown";
import PhotoCarousel from "@/Components/Events/PhotoCarousel";
import {
    EventTypeBecomingAKingExperience,
    EventTypeBecomingAKingRetreat,
    EventTypeCaptivatingCoreRetreat, EventTypeCaptivatingExperience,
    EventTypeFires,
    EventTypeWahBasicRetreat, EventTypeWahExperience
} from "@/helpers/eventNames";
import ShareEvent from "@/Components/Events/ShareEvent";
import {ShareIcon} from "@heroicons/react/24/outline";

const ViewEvent = () => {
    const page = usePage();
    const event = page.props.event as Event;
    const {auth} = page.props as unknown as PageProps;

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
    })

    const eventTitle = (event: Event) => {

        switch (event.eventType) {
            case EventTypeFires:
                return `${event.name} Fire`;
            case EventTypeWahBasicRetreat:
                return `Wild at Heart BASIC Retreat`;
            case EventTypeCaptivatingCoreRetreat:
                return `Captivating CORE Retreat`;
            case EventTypeBecomingAKingRetreat:
                return `Becoming a King Retreat`;
            case EventTypeWahExperience:
                return `Wild at Heart Experience`;
            case EventTypeCaptivatingExperience:
                return `Captivating Experience`;
            case EventTypeBecomingAKingExperience:
                return `Becoming a King Experience`;
            default:
                return `${event.name} hosted by ${event.host} in ${event.location}`;
        }
    }

    const eventImages = (event: Event) => {
        switch (event.eventType) {
            case EventTypeFires:
                return ['/img/fires/Image2.webp', '/img/fires/Image3.webp', '/img/fires/Image.webp', '/img/fires/Image4.webp'];
            default:
                return [];

        }
    }

    const eventDate = (event: Event) => {
        switch (event.eventType) {
            case EventTypeFires:
                return '';
            case EventTypeCaptivatingExperience
            || EventTypeWahExperience
            || EventTypeBecomingAKingExperience:
                return `Starts on ${event.startDate}`;
            default:
                return `${event.startDate} - ${event.endDate}`;
        }
    }


    return (
        <AppLayout title={`${event.name} hosted by ${event.host} in ${event.location}`}
                   showFooter={false}>
            <div className={'relative'}>
                <StaticMap lng={event.longitude} lat={event.latitude} event={event}/>
                <BackToEvents/>
                <ShareEvent
                    className={`absolute top-[20px] right-4 bg-white z-10 flex items-center space-x-2 rounded-full px-4 py-2 text-black font-medium hover:text-orange transition-colors duration-200 ease-in-out`}/>
            </div>
            <div className="relative top-[-32px] bg-black rounded-t-[2.5rem] border-t p-6 text-white border-grey-darker"
                 id={'event'}>
                <div className="container mx-auto">
                    <div className="md:space-x-8 md:flex">
                        <div className="md:w-2/3">

                            <div className="flex items-center space-x-4">
                                <h1 className="mb-1 flex items-center text-3xl leading-tighter">
                                    {eventTitle(event)}
                                    {!event.approved_at && (
                                        <span
                                            className="ml-2 rounded px-2 py-1 text-sm font-semibold uppercase text-white bg-orange">
                                            Unpublished
                                        </span>
                                    )}
                                </h1>
                            </div>

                            <div className="md:flex md:items-start md:space-x-4 space-y-4 md:space-y-0 mt-4">
                                <Location location={event.location}/>
                                {eventDate(event) && (
                                    <Date date={eventDate(event)}/>
                                )}

                            </div>

                            {event.eventTypeData.hero_video && (
                                <VideoPlayer src={event.eventTypeData.hero_video}
                                             className={`mt-[48px]`}/>
                            )}

                            {event.eventTypeData.hero_image && (
                                <img src={event.eventTypeData.hero_image}
                                     className={`mt-[48px]`} alt={``}/>
                            )}

                            {event.eventTypeData.key_details && event.eventTypeData.key_details.length > 0 && (
                                <div>
                                    <HeadingOne className={`mt-[48px]`}>Key Details</HeadingOne>
                                    <ul className={`my-8`}>
                                        {event.eventTypeData.key_details.map((item: any) => (
                                            <li className="flex items-start mb-4" key={item.id}>
                                                <div dangerouslySetInnerHTML={{__html: item.icon}}
                                                     className={`w-6 h-6`}/>
                                                <div className="ml-[24px] leading-[24px]">
                                                    <p className="font-medium">{item.headline}</p>
                                                    <p className="text-sm text-[#B8B8B8]">
                                                        {item.description}
                                                    </p>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )
                            }

                            <div
                                className={`mt-4 prose prose-p:text-white prose-a:text-orange max-w-none prose-hr:bg-black prose-h2:text-white prose-h3:text-white  prose-li:text-white`}>
                                <Markdown>
                                    {event.eventTypeData.description}
                                </Markdown>
                            </div>

                            {eventImages(event).length > 0 && (
                                <PhotoCarousel
                                    className={`mt-8`}
                                    images={eventImages(event)}/>
                            )}

                        </div>

                        <div className="hidden md:block md:w-1/3 relative">
                            <Host host={event.host} hostPhoto={event.hostPhoto}/>

                            <ConnectWithHost className={'mt-6 sticky top-4'}/>

                            {(!event.gift_requested && auth.user && auth.user.id === event.hostId) && (
                                <UpdateAddress/>
                            )}

                        </div>
                    </div>
                </div>
            </div>

            <MobileConnect/>

        </AppLayout>
    )
};

export default ViewEvent;
