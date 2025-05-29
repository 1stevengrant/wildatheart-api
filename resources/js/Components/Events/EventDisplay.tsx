import React, { useEffect } from "react";

import { usePage } from "@inertiajs/react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { EventTypeBadge } from "@/Components/Events/EventTypeBadge";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";
import { formatDate } from "@/helpers/formatDate";

export const EventDisplay = ({
    event,
    style = {},
    className = "",
    startDate = "",
    frequency = "",
}: {
    style?: React.CSSProperties;
    event: App.Data.EventData;
    className?: string;
    startDate?: string | null | undefined;
    frequency?: string | null | undefined;
}) => {
    useEffect(() => {}, [event]);
    const { props } = usePage();
    const eventType = props.eventType as App.Data.EventTypeData;

    return (
        <div
            style={style}
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
            {eventType ? (
                <Image
                    src={eventType.image}
                    className={`rounded-md flex-grow-0`}
                />
            ) : (
                <Image
                    src={event.event_type_image}
                    className={`rounded-md flex-grow-0`}
                />
            )}

            <EventInfo>
                <div className={`leading-tight`}>
                    <div
                        className={`flex justify-between items-center space-x-2`}
                    >
                        <EventName name={event.display_name} />
                        <EventTypeBadge
                            className={`bg-white text-sm text-n500 capitalize`}
                        >
                            {event ? (
                                <>{event.badge_display}</>
                            ) : (
                                <>
                                    {startDate
                                        ? `${formatDate(startDate)}`
                                        : frequency}
                                </>
                            )}
                        </EventTypeBadge>
                    </div>
                    <EventLocation location={event.location} />
                </div>
                <EventHostInfo>
                    <UserAvatar image={event.host_photo} />
                    <HostedBy host={event.host} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};
