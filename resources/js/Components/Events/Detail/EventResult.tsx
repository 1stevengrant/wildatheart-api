import { Link } from "@inertiajs/react";

import { EventCardContent } from "../EventCardContent";

export const EventResult = ({
    event,
    className = "",
    onClick,
    onMouseEnter,
    onMouseLeave,
}: {
    event: App.Data.EventData;
    className?: string;
    onClick?: (event: App.Data.EventData) => void;
    onMouseEnter?: (event: any) => void;
    onMouseLeave?: (event: any) => void;
}) => {
    return (
        <div
            className={`relative hidden rounded-md bg-black p-2 shadow-lg md:block md:p-4 border hover:border-p200 ${className}`}
            onClick={() => (onClick ? onClick(event) : null)}
            onMouseEnter={() => onMouseEnter?.(event)}
            onMouseLeave={() => onMouseLeave?.(null)}
        >
            {event.is_featured && event.custom_landing_url ? (
                <a
                    href={event.custom_landing_url}
                    className="clear-both flex items-stretch space-x-4"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <EventCardContent event={event} />
                </a>
            ) : (
                <Link
                    href={event.url}
                    className="clear-both flex items-stretch space-x-4"
                >
                    <EventCardContent event={event} />
                </Link>
            )}
        </div>
    );
};
