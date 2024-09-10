import React, { useEffect } from "react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { HostedBy } from "@/Components/Events/HostedBy";
import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { imageGenerator } from "@/helpers/user";

export const EventDisplay = ({
    event,
    className,
}: {
    event: any;
    className?: string;
}) => {
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
                    <FallbackAvatar
                        name={event.hostObject.full_name}
                        md5email={event.hostObject.md5email}
                        size={24}
                    />

                    <HostedBy host={event.host} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};
