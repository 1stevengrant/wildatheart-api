import React from "react";

import { Link } from "@inertiajs/react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

export const HostNewEvent = () => {
    return (
        <div className="mt-6 flex items-center justify-center">
            <Link href={route("host")} className={`${primaryButtonClasses}`}>
                Host a New Event
            </Link>
        </div>
    );
};
