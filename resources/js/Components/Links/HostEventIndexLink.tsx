import React from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { EventType } from "@/types/EventType";

export const HostEventIndexLink = ({ eventType }: { eventType: EventType }) => {
    return (
        <Link
            href={route("host.eventType.index", eventType)}
            className={circularButtonClasses}
        >
            <ArrowLeftIcon className={`w-4 h-4`} />
        </Link>
    );
};
