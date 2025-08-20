import { Link } from "@inertiajs/react";

import { FeaturedBadge } from "@/Components/Events/FeaturedBadge";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";

export const MobileEventListCard = ({
    event,
}: {
    event: App.Data.EventData;
}) => {
    const linkContent = (
        <>
            <div className="space-y-2">
                <Image
                    src={event.event_type_image}
                    alt={event.name ?? ""}
                    className={`w-24 h-24 rounded-lg flex-shrink-0`}
                />
                <FeaturedBadge
                    event={event}
                />
            </div>
            <div className={`flex-1 flex flex-col justify-between`}>
                <div className={`leading-tighter text-xxs`}>
                    <p className={`text-n40`}>{event.card_title}</p>
                    <p className={`font-medium text-base mb-3`}>
                        {event.location}
                    </p>
                    <span
                        className={`px-4 py-0.5 rounded-full border border-white inline-block mb-1`}
                    >
                        {event.badge_display}
                    </span>
                </div>
                <div className={`flex items-center space-x-2 mt-1`}>
                    {event.host_photo && (
                        <UserAvatar image={event.host_photo} />
                    )}
                    <div
                        className={`flex flex-col text-n40 leading-4 text-sm`}
                    >
                        {event.type_group === fireTypeGroup && (
                            <div>{event.name} Fire</div>
                        )}
                        <div>{event.host_name}</div>
                    </div>
                </div>
            </div>
        </>
    );

    return (
        <li className={`border border-n400 rounded-lg p-4 relative`}>
            {event.custom_landing_url ? (
                <a
                    className={`flex space-x-4`}
                    href={event.custom_landing_url}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {linkContent}
                </a>
            ) : (
                <Link className={`flex space-x-4`} href={event.url}>
                    {linkContent}
                </Link>
            )}
        </li>
    );
};
