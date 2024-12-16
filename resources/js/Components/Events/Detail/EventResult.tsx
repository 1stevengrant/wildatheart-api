import { Link } from "@inertiajs/react";

import { EventCardContent } from "../EventCardContent";

import { IEventCardProps } from "@/types/IEventCardProps";

export const EventResult = ({
    event,
    className = "",
    onClick,
    onMouseEnter,
    onMouseLeave,
}: IEventCardProps) => {
    return (
        <div
            className={`relative hidden rounded-md bg-black p-2 shadow-lg md:block md:p-4 border hover:border-p200 ${className}`}
            onClick={() => (onClick ? onClick(event) : null)}
            onMouseEnter={() => onMouseEnter?.(event)}
            onMouseLeave={() => onMouseLeave?.(null)}
        >
            <Link
                href={event.url}
                className="clear-both flex items-stretch space-x-4"
            >
                <EventCardContent event={event} />
            </Link>
        </div>
    );
};
