import React, {useEffect} from "react";
import AppLayout from "../Layouts/AppLayout";
import StaticMap from "@/Components/Maps/StaticMap";
import {usePage} from "@inertiajs/react";
import {Event} from "@/types/Event";
import BackToEvents from "@/Components/Buttons/BackToEvents";
import PhotoCarouselMobile from "@/Components/Events/PhotoCarouselMobile";
import PhotoCarousel from "@/Components/Events/PhotoCarousel";
import {
    BookOpenIcon,
    CalendarDaysIcon,
    MegaphoneIcon,
    ShieldCheckIcon,
    SparklesIcon, UserCircleIcon
} from "@heroicons/react/24/outline";
import PoopIcon from "@/Components/Icons/Poop";
import Guideline from "@/Components/Events/Guideline";
import MetaItem from "@/Components/Events/MetaItem";
import ShareEvent from "@/Components/Events/ShareEvent";
import ConnectWithHost from "@/Components/Events/ConnectWithHost";
import UpdateAddress from "@/Components/Events/UpdateAddress";
import MobileConnect from "@/Components/Events/MobileConnect";
import {PageProps} from "@/types";
import UserAvatar from "@/Components/UserAvatar";

const iconSize = 'w-6 h-6 flex-shrink-0';
const metaItems = [
    {
        icon: <SparklesIcon className={iconSize}/>,
        title: 'Outdoor Event',
        description: 'This event is slated to be an outdoor event. Please dress accordingly.'
    },
    {
        icon: <CalendarDaysIcon className={iconSize}/>,
        title: 'Frequency',
        description: 'The vision of Fires is that they’re monthly gatherings. However, each Host is free to schedule his Fire as best suits the local context.'
    }
]

const guidelines = [
    {
        icon: <MegaphoneIcon className={iconSize}/>,
        title: '1. Fires aren’t religious',
        description: 'Leave your religious and professional credentials behind.'
    },
    {
        icon: <BookOpenIcon className={iconSize}/>,
        title: '2. Your Story Matters',
        description: 'Fires are not a place for teaching or a podium for experts.'
    },
    {
        icon: <PoopIcon className={iconSize}/>,
        title: '3. No bullshit allowed',
        description: 'Fires are a place for authentic masculinity. No posing.'
    },
    {
        icon: <ShieldCheckIcon className={iconSize}/>,
        title: '4. Confidentiality is essential',
        description: 'What is shared at the Fire, stays at the Fire.'
    }
]

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

    return (
        <AppLayout title={`${event.name} hosted by ${event.host} in ${event.location}`}
                   showFooter={false}>
            <div className={'relative'}>
                <StaticMap lng={event.longitude} lat={event.latitude}/>
                <BackToEvents/>
            </div>
            <div className="rounded-t-2xl border-t p-6 text-white border-grey-darker" id={'event'}>
                <div className="container mx-auto">
                    <div className="md:space-x-8 md:flex">
                        <div className="md:w-2/3">
                            <div className="flex items-center space-x-4">
                                <h1 className="mb-1 flex items-center text-3xl leading-tighter">
                                    {event.name} Fire
                                    {!event.approved_at && (
                                        <span className="ml-2 rounded px-2 py-1 text-sm font-semibold uppercase text-white bg-orange">
                                            Unpublished
                                        </span>
                                    )}
                                </h1>
                            </div>

                            <h2 className="mb-8 text-xl leading-tighter">
                                {event.location}
                            </h2>

                            <div className="mt-10 flex items-start space-x-4">
                                {event.hostPhoto ? (
                                    <UserAvatar
                                        image={event.hostPhoto}
                                        width={400}
                                        className={`w-20 h-20`}/>) : (
                                    <UserCircleIcon className={'w-20 h-20'}/>
                                )}

                                <div>
                                    <p className="text-sm">Your Host ·</p>
                                    <p className="text-2xl font-medium">{event.host}</p>
                                </div>
                            </div>

                            <div className="border-t border-b py-8 mt-[25px] space-y-4 border-grey-darker">
                                {metaItems.map((metaItem, index) => (
                                    <MetaItem key={index} icon={metaItem.icon} title={metaItem.title}
                                              description={metaItem.description}/>
                                ))}
                            </div>

                            <div className="mt-8 pb-12 space-y-4 md:pb-0">
                                <p className="font-semibold">Guidelines for Fire participants:</p>

                                <div className="pt-6 space-y-6">
                                    {guidelines.map((guideline, index) => (
                                        <Guideline key={index}
                                                   icon={guideline.icon}
                                                   title={guideline.title}
                                                   description={guideline.description}/>
                                    ))}
                                </div>
                            </div>
                            <PhotoCarousel/>
                            <PhotoCarouselMobile/>
                        </div>

                        <div className="hidden md:block md:w-1/3">
                            <ShareEvent/>

                            <ConnectWithHost className={'mt-6'}/>

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
