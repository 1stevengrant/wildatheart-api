import React from "react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { EventTypeBadge } from "@/Components/Events/EventTypeBadge";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";

export const EventDisplay = ({
    image,
    name,
    badge,
    location,
    hostName,
    hostPhoto,
    style = {},
    className = "",
}: {
    image: string;
    name: string;
    badge?: string | null;
    location: string;
    hostName: string;
    hostPhoto: string;
    style?: React.CSSProperties;
    className?: string;
}) => {
    return (
        <div
            style={style}
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
            <Image src={image} className={`rounded-md flex-grow-0`} />

            <EventInfo>
                <div className={`leading-tight`}>
                    <div
                        className={`flex justify-between items-center space-x-2`}
                    >
                        <EventName name={name} />
                        <EventTypeBadge
                            className={`bg-white text-sm text-n500 capitalize`}
                        >
                            {badge}
                        </EventTypeBadge>
                    </div>
                    <EventLocation location={location} />
                </div>
                <EventHostInfo>
                    <UserAvatar image={hostPhoto} />
                    <HostedBy host={hostName} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};
