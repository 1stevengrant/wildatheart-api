import React, { useEffect } from "react";

import { UserCircleIcon } from "@heroicons/react/24/outline";

import EventHostInfo from "@/Components/Events/EventHostInfo";
import EventInfo from "@/Components/Events/EventInfo";
import EventLocation from "@/Components/Events/EventLocation";
import EventName from "@/Components/Events/EventName";
import { HostedBy } from "@/Components/Events/HostedBy";
import { UserAvatar } from "@/Components/UserAvatar";
import { imageGenerator } from "@/helpers/user";

interface EventDisplayProps {
    event: any;
    className?: string;
}

const EventDisplay = ({ event, className }: EventDisplayProps) => {
    useEffect(() => {}, [event]);
    return (
        <div
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-grey-border`}
        >
            {event.eventTypeImage && (
                <img
                    src={imageGenerator(event.eventTypeImage, {
                        width: 100,
                        height: 100,
                    })}
                    className={`rounded-md flex-grow-0`}
                    alt=""
                />
            )}
            <EventInfo>
                <div>
                    <EventName name={event.name} />
                    <EventLocation location={event.location} />
                </div>
                <EventHostInfo>
                    <>
                        {event.hostPhoto ? (
                            <UserAvatar image={event.hostPhoto} width={32} />
                        ) : (
                            <UserCircleIcon className={"w-6 h-6 text-white"} />
                        )}
                    </>
                    <HostedBy host={event.host} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};

export default EventDisplay;
