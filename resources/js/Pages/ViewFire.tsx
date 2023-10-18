import React, {useEffect} from "react";
import AppLayout from "../Layouts/AppLayout";
import StaticMap from "@/Components/Maps/StaticMap";
import {usePage} from "@inertiajs/react";
import {Fire} from "@/types/Fire";
import BackToFires from "@/Components/Buttons/BackToFires";
import PhotoCarouselMobile from "@/Components/Fires/PhotoCarouselMobile";
import PhotoCarousel from "@/Components/Fires/PhotoCarousel";
import {
    BookOpenIcon,
    CalendarDaysIcon,
    MegaphoneIcon,
    ShieldCheckIcon,
    SparklesIcon
} from "@heroicons/react/24/outline";
import PoopIcon from "@/Components/Icons/Poop";
import Guideline from "@/Components/Fires/Guideline";
import MetaItem from "@/Components/Fires/MetaItem";
import ShareFire from "@/Components/Fires/ShareFire";
import ConnectWithHost from "@/Components/Fires/ConnectWithHost";
import UpdateAddress from "@/Components/Fires/UpdateAddress";
import MobileConnect from "@/Components/Fires/MobileConnect";
import {PageProps} from "@/types";

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

const ViewFire = () => {
    const page = usePage();
    const fire = page.props.campfire as Fire;
    const {auth} = page.props as unknown as PageProps;

    useEffect(() => {
        if (window.innerWidth < 768) {
            const campfireElement = document.getElementById("campfire"),
                connectWithHostElement = document.getElementById(
                    "mobile-connect-with-host"
                );

            if (!campfireElement || !connectWithHostElement) return;

            campfireElement.style.paddingBottom =
                connectWithHostElement.offsetHeight + 16 + "px";
        }
    })

    return (
        <AppLayout title={`${fire.name} hosted by ${fire.host} in ${fire.location}`}
                   showFooter={false}>
            <div className={'relative'}>
                <StaticMap lng={fire.longitude} lat={fire.latitude}/>
                <BackToFires/>
            </div>
            <div className="rounded-t-2xl border-t border-grey-darker p-6 text-white" id={'campfire'}>
                <div className="container mx-auto">
                    <div className="md:flex md:space-x-8">
                        <div className="md:w-2/3">
                            <div className="flex items-center space-x-4">
                                <h1 className="mb-1 text-3xl leading-tighter">
                                    {fire.name} Fire
                                </h1>
                            </div>

                            <h2 className="mb-8 text-xl leading-tighter">
                                {fire.location}
                            </h2>

                            <div className="mt-10 flex items-start space-x-4">
                                <div className="h-20 w-20 flex-shrink-0 rounded-full bg-cover bg-center object-fill"
                                     style={{backgroundImage: `url('/svgs/user.svg')`}}/>
                                <div>
                                    <p className="text-sm">Your Host ·</p>
                                    <p className="text-2xl font-medium">{fire.host}</p>
                                </div>
                            </div>

                            <div className="mt-[25px] space-y-4 border-b border-t border-grey-darker py-8">
                                {metaItems.map((metaItem, index) => (
                                    <MetaItem key={index} icon={metaItem.icon} title={metaItem.title}
                                              description={metaItem.description}/>
                                ))}
                            </div>

                            <div className="mt-8 space-y-4 pb-12 md:pb-0">
                                <p className="font-semibold">Guidelines for Fire participants:</p>

                                <div className="space-y-6 pt-6">
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
                            <ShareFire/>

                            <ConnectWithHost className={'mt-6'}/>

                            {(!fire.gift_requested && auth.user && auth.user.id === fire.hostId) && (
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

export default ViewFire;
