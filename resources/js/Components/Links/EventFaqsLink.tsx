import { ArrowRightIcon } from "@heroicons/react/16/solid";
import { Link } from "@inertiajs/react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

export const EventFaqsLink = ({ eventType }: { eventType: any }) => {
    return (
        <Link
            className={primaryButtonClasses}
            href={route("host.eventType.faqs", eventType)}
        >
            FAQs
            <ArrowRightIcon className={`w-4 h-4 ml-2`} />
        </Link>
    );
};
