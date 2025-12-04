import { Link } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { EventCardContent } from "./EventCardContent";

import { isSelectedEvent } from "@/helpers/isSelectedEvent";
import { currentEventState } from "@/store/eventsAtom";
export const EventCard = ({
    event,
    className = "",
    attrs,
}: {
    event: App.Data.EventMapData;
    className?: string;
    attrs?: any;
}) => {
    const [currentEvent] = useRecoilState(currentEventState);

    return (
        <div
            className={`relative rounded-md bg-black p-2 shadow-lg md:p-4 ${className} max-w-[520px] border-2 ${isSelectedEvent(event, currentEvent)}`}
            {...attrs}
        >
            <Link
                href={event.url}
                className={`clear-both flex items-stretch space-x-4 focus:outline-none focus:border-none`}
            >
                <EventCardContent event={event} />
            </Link>
        </div>
    );
};
