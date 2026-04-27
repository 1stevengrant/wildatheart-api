import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { Link } from "@inertiajs/react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

export const EventScheduleLink = ({
    eventType,
}: {
    eventType: App.Data.EventTypeData;
}) => {
    return (
        <Link
            className={primaryButtonClasses}
            href={route("host.eventType.schedule", eventType)}
        >
            {eventType.schedule_nav_text_override ?? `Schedule`}
            <ArrowRightIcon className={`w-4 h-4 ml-2`} />
        </Link>
    );
};
