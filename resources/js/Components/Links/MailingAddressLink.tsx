import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { Link } from "@inertiajs/react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { EventType } from "@/types/EventType";

export const MailingAddressLink = ({ eventType }: { eventType: EventType }) => {
    return (
        <Link
            className={primaryButtonClasses}
            href={route("host.eventType.mailing", eventType)}
        >
            Mailing Address
            <ArrowRightIcon className="ml-2 h-4 w-4" />
        </Link>
    );
};
