import { Link } from "@inertiajs/react";
import pluralize from "pluralize";
import { useRecoilState } from "recoil";

import { getRouteForEventType } from "@/helpers/getRouteForEventType";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { selectedEventTypeState } from "@/store/eventsAtom";

export const HostNewEvent = () => {
    const [selectedEventType] = useRecoilState(selectedEventTypeState);

    const eventTypeName = pluralize.singular(
        selectedEventType?.name ?? `Event`
    );

    return (
        <div className="mt-6 flex items-center justify-center">
            <Link
                href={`${selectedEventType ? getRouteForEventType(selectedEventType.slug) : route("host.index")}`}
                className={`${primaryButtonClasses}`}
            >
                Host a New {eventTypeName}
            </Link>
        </div>
    );
};
