import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { Link } from "@inertiajs/react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

export const MailingAddressLink = ({
    eventType,
}: {
    eventType: App.Data.EventTypeData;
}) => {
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
