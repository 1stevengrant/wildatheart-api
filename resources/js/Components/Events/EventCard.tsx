import React from 'react';
import EventCardContent from "./EventCardContent";
import {GeoHit} from "@/types/EventHit";
import {Link} from "@inertiajs/react";

interface EventCardProps {
    event: GeoHit;
    className?: string;
}

const EventCard: React.FC<EventCardProps> = ({event, className}) => {
    return (
        <div className={`relative hidden rounded-md bg-black p-2 shadow-lg md:block md:p-4 ${className} max-w-[520px]`}>
            <Link href={event.url}
               className="clear-both flex items-stretch space-x-4">
                <EventCardContent event={event}/>
            </Link>
        </div>
    );
};
export default EventCard
