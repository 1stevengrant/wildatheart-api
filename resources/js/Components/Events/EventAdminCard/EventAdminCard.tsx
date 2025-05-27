import { NewspaperIcon, UserGroupIcon } from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";

import { EventAdminActions } from "@/Components/Events/EventAdminCard/EventAdminActions";
import { EventPublicPrivate } from "@/Components/Events/EventAdminCard/EventPublicPrivate";
import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventTypeImage } from "@/Components/Events/EventTypeImage";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";

const statusBadge = (approvedAt?: string | null) => (
    <span
        className={`text-xxs px-2 py-1 rounded-full ${
            approvedAt ? "bg-p200" : "bg-n700"
        } text-white`}
    >
        {approvedAt ? "Published" : "Unpublished"}
    </span>
);

const footerLinks = (id: string) => [
    { href: route("resources"), Icon: NewspaperIcon, label: "Resources" },
    {
        href: route("my.contacts", { event_id: id }),
        Icon: UserGroupIcon,
        label: "Contacts",
    },
];

export const EventAdminCard = ({ event }: { event: App.Data.EventData }) => {
    const linkClasses =
        "hover:bg-p200 bg-n900 rounded flex items-center space-x-2 capitalize px-2 md:px-4 py-2 font-semibold";

    return (
        <div>
            <div className="rounded-lg p-4 border border-n700 relative">
                {event.type_group !== fireTypeGroup && (
                    <EventPublicPrivate event={event} />
                )}
                <div className="flex space-x-4 mt-2">
                    <EventTypeImage event={event} />
                    <div className="md:flex-1 md:flex md:flex-col md:justify-between">
                        <EventTypeGroupIndicator event={event} />
                        <div>
                            <p className="text-white flex items-center space-x-2">
                                {event.type_group !== fireTypeGroup && (
                                    <span>{event.display_name}</span>
                                )}
                                {statusBadge(event.approved_at)}
                            </p>
                            <p className="text-sm text-white">
                                {event.location}
                            </p>
                        </div>
                        <p className="text-sm leading-tight text-n40 mt-2">
                            Hosted by {event.host}
                        </p>
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-between bg-black border-b border-l border-n700 border-r px-4 -mt-2 py-4 rounded-b-lg relative">
                <div className="flex items-center space-x-2">
                    {footerLinks(event.id).map(({ href, Icon, label }) => (
                        <Link key={label} href={href} className={linkClasses}>
                            <Icon className={defaultIconSize} />
                            <span>{label}</span>
                        </Link>
                    ))}
                </div>
                <EventAdminActions event={event} />
            </div>
        </div>
    );
};
