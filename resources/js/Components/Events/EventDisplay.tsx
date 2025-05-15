import { useEffect } from "react";

import { usePage } from "@inertiajs/react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { EventTypeBadge } from "@/Components/Events/EventTypeBadge";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";

export const EventDisplay = ({
    event,
    className = "",
    startDate = "",
    frequency = "",
}: {
    event: App.Data.EventData;
    className?: string;
    startDate?: string | null | undefined;
    frequency?: string | null | undefined;
}) => {
    useEffect(() => {}, [event]);
    const { props } = usePage();
    const eventType = props.eventType as App.Data.EventTypeData;

    const formatDate = (dateString: string | null | undefined): string => {
        if (!dateString) return "";

        try {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.toLocaleDateString("en-US", { month: "long" });

            // Add ordinal suffix (st, nd, rd, th)
            const getOrdinalSuffix = (day: number): string => {
                if (day >= 11 && day <= 13) return "th";
                switch (day % 10) {
                    case 1:
                        return "st";
                    case 2:
                        return "nd";
                    case 3:
                        return "rd";
                    default:
                        return "th";
                }
            };

            return `${day}${getOrdinalSuffix(day)} ${month}`;
        } catch (error) {
            return dateString;
        }
    };

    return (
        <div
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
            <Image src={eventType.image} className={`rounded-md flex-grow-0`} />
            <EventInfo>
                <div className={`leading-tight`}>
                    <div
                        className={`flex justify-between items-center space-x-2`}
                    >
                        <EventName name={event.name} />
                        <EventTypeBadge
                            className={`bg-white text-sm text-n500 capitalize`}
                        >
                            {startDate ? `${formatDate(startDate)}` : frequency}
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
