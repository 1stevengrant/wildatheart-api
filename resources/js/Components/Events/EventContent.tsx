import React from 'react';
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import HostedBy from "@/Components/Events/HostedBy";
import EventName from "@/Components/Events/EventName";
import EventLocation from "@/Components/Events/EventLocation";
import EventImage from "@/Components/Events/EventImage";
import EventHostInfo from "@/Components/Events/EventHostInfo";
import EventInfo from "@/Components/Events/EventInfo";
import {GeoHit} from "@/types/EventHit";
import {Event} from "@/types/Event";

interface EventContentProps {
    event: GeoHit | undefined | Event | null;
}

const EventContent = (props: EventContentProps) => {
    return (
        <>
            <EventImage/>
            <EventInfo>
                <div>
                    <EventName name={props.event?.name}/>
                    <EventLocation location={props.event?.location}/>
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
                    <HostedBy host={props.event?.host}/>
                </EventHostInfo>
            </EventInfo>
        </>
    );
}
export default EventContent
