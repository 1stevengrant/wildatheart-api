import { useEffect } from "react";

import { usePage } from "@inertiajs/react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";

export const EventDisplay = ({
    event,
    className = "",
}: {
    event: App.Data.EventData;
    className?: string;
}) => {
    useEffect(() => {}, [event]);
    const { props } = usePage();
    const eventType = props.eventType as App.Data.EventTypeData;

    return (
        <div
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
            <Image src={eventType.image} className={`rounded-md flex-grow-0`} />
            <EventInfo>
                <div className={`leading-tight`}>
                    <EventName name={event.name} />
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
