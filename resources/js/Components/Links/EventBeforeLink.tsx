import React from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { EventType } from "@/types/EventType";

export const EventBeforeLink = ({ eventType }: { eventType: EventType }) => {
    return (
        <Link
            className={circularButtonClasses}
            href={route("host.eventType.before", eventType)}
        >
            <ArrowLeftIcon className={`w-4 h-4`} />
        </Link>
    );
};
