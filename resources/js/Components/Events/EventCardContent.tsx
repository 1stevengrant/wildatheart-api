import React from 'react';
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import HostedBy from "@/Components/Events/HostedBy";
import EventLocation from "@/Components/Events/EventLocation";
import EventHostInfo from "@/Components/Events/EventHostInfo";
import EventInfo from "@/Components/Events/EventInfo";
import {GeoHit} from "@/types/EventHit";
import {Event} from "@/types/Event";
import EventTypeBadge from "@/Components/Events/EventTypeBadge";
import {
    EventTypeFires,
} from "@/helpers/eventNames";

import {userPhoto} from "@/helpers/user";

interface EventCardContentProps {
    event: GeoHit | undefined | Event | null;
}

const EventCardContent = (props: EventCardContentProps) => {
    const eventTypeClasses = `text-grey-200 font-semibold text-sm`,
        eventTypeNameClasses = `flex items-center justify-between space-x-4`

    return (
        <>
            {props.event?.eventTypeImage && (
                <img src={userPhoto(props.event?.eventTypeImage, {
                    width: 100,
                    height: 100,
                })}
                     className={`rounded-md flex-grow-0`}
                     alt=""/>
            )}

            <EventInfo>
                <div>
                    {props.event?.eventType === EventTypeFires && (
                        <div className={eventTypeNameClasses}>
                            <div className={eventTypeClasses}>
                                Monthly Gathering for Men
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                Monthly
                            </EventTypeBadge>
                        </div>

                    )}
                    {props.event?.eventType.includes('retreat') && (
                        <div className={eventTypeNameClasses}>
                            <div className={eventTypeClasses}>
                                Multi-Day retreat for  {props.event?.sex === 'm' ? <span>Men</span> :
                                <span>Women</span>}
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                {props.event?.startDate} - {props.event.endDate}
                            </EventTypeBadge>
                        </div>
                    )}
                    {props.event?.eventType.includes('experience') && (
                        <div className={eventTypeNameClasses}>
                            <div className={eventTypeClasses}>
                                6-part video study for {props.event?.sex === 'm' ? <span>Men</span> :
                                <span>Women</span>}
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                Starts {props.event?.startDate}
                            </EventTypeBadge>
                        </div>
                    )}

                    <EventLocation location={props.event?.location}/>

                    {props.event?.eventType === EventTypeFires && (
                        <EventTypeBadge className={`my-2 inline-flex md:hidden`}>
                            Monthly
                        </EventTypeBadge>
                    )}

                    {props.event?.eventType.includes('experience') && (
                        <EventTypeBadge className={`my-2 inline-flex md:hidden`}>
                            Starts {props.event?.startDate}
                        </EventTypeBadge>
                    )}

                    {props.event?.eventType.includes('retreat') && (
                        <EventTypeBadge className={`my-2 inline-flex md:hidden`}>
                            Starts {props.event?.startDate}
                        </EventTypeBadge>
                    )}

                </div>
                <EventHostInfo>
                    <>
                        {props.event?.hostPhoto ? (
                            <UserAvatar
                                image={props.event?.hostPhoto}
                                width={32}/>
                        ) : (
                            <UserCircleIcon className={'w-6 h-6 text-white'}/>
                        )}
                    </>
                    <div className="text-xs text-grey-200 leading-tighter">
                        {props.event?.eventType === EventTypeFires &&
                            <div>{props.event?.name} Fire</div>
                        }
                        <HostedBy host={props.event?.host}/>
                    </div>
                </EventHostInfo>
            </EventInfo>
        </>
    );
}
export default EventCardContent
