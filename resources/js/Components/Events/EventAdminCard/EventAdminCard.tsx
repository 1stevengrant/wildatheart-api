import { NewspaperIcon, UserGroupIcon } from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";

import { EventAdminActions } from "@/Components/Events/EventAdminCard/EventAdminActions";
import { EventPublicPrivate } from "@/Components/Events/EventAdminCard/EventPublicPrivate";
import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventTypeImage } from "@/Components/Events/EventTypeImage";
import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import { EventTypeFires } from "@/helpers/eventNames";
import { Event } from "@/types/Event";

export const EventAdminCard = ({ event }: { event: Event }) => {
    const eventLinkClasses = `hover:bg-orange bg-[#353535] rounded flex items-center space-x-2 capitalize px-4 py-2 font-semibold`;

    return (
        <div>
            <div
                className={`rounded-lg p-4 border border-grey-border relative`}
            >
                {event.type_group !== "fire" && (
                    <EventPublicPrivate event={event} />
                )}
                <div className={`flex space-x-4 mt-2`}>
                    <EventTypeImage event={event} />
                    <div className={`flex-1 flex flex-col justify-between`}>
                        <EventTypeGroupIndicator event={event} />
                        <div>
                            <p className="text-white">
                                {event.name}{" "}
                                {event.eventType === EventTypeFires && (
                                    <span>Fire</span>
                                )}
                            </p>

                            <p className="text-sm text-white">
                                {event.location}
                            </p>
                        </div>
                        <div className={`flex items-center space-x-2 mt-2`}>
                            <FallbackAvatar
                                size={24}
                                name={event.hostObject.full_name}
                                md5email={event.hostObject.md5email}
                            />
                            <div className="text-sm leading-tighter text-grey-200">
                                Hosted by {event.host}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                className={`flex items-center justify-between bg-[#292929] px-4 -mt-2 py-4 relative rounded-b-lg`}
            >
                <div className={`flex items-center space-x-2`}>
                    <Link
                        href={route("resources")}
                        className={eventLinkClasses}
                    >
                        <NewspaperIcon className={defaultIconSize} />
                        <span>Resources</span>
                    </Link>
                    <Link
                        className={eventLinkClasses}
                        href={route("my.connections", { event_id: event.id })}
                    >
                        <UserGroupIcon className={defaultIconSize} />
                        <span>Contacts</span>
                    </Link>
                </div>
                <EventAdminActions event={event} />
            </div>
        </div>
    );
};
