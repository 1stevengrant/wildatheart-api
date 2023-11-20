import React, {useEffect} from "react";
import UserAvatar from "@/Components/UserAvatar";
import {UserCircleIcon} from "@heroicons/react/24/outline";
import HostedBy from "@/Components/Events/HostedBy";
import EventImage from "@/Components/Events/EventImage";
import EventName from "@/Components/Events/EventName";
import EventLocation from "@/Components/Events/EventLocation";
import EventHostInfo from "@/Components/Events/EventHostInfo";
import EventInfo from "@/Components/Events/EventInfo";

interface EventDisplayProps {
    event: any;
    className?: string;
}

const EventDisplay = ({event, className}: EventDisplayProps) => {
    useEffect(() => {

    }, [event]);
    return (
        <div className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-grey-border`}>
            <EventImage/>
            <EventInfo>
                <div>
                    <EventName name={event.name}/>
                    <EventLocation location={event.location}/>
                </div>
                <EventHostInfo>
                    <>
                        {event.hostPhoto ? (
                            <UserAvatar
                                image={event.hostPhoto}
                                width={32}/>
                        ) : (
                            <UserCircleIcon className={'w-6 h-6 text-white'}/>
                        )}
                    </>
                    <HostedBy host={event.host}/>
                </EventHostInfo>
            </EventInfo>
        </div>
    );
}

export default EventDisplay;
