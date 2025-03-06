import { NewspaperIcon, UserGroupIcon } from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";

import { EventAdminActions } from "@/Components/Events/EventAdminCard/EventAdminActions";
import { EventPublicPrivate } from "@/Components/Events/EventAdminCard/EventPublicPrivate";
import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventTypeImage } from "@/Components/Events/EventTypeImage";

import { defaultIconSize } from "@/helpers/defaultIconSize";
import { EventTypeFires } from "@/helpers/eventNames";
import { Event } from "@/types/Event";

export const EventAdminCard = ({ event }: { event: Event }) => {
    const eventLinkClasses = `hover:bg-p200 bg-[#353535] rounded flex items-center space-x-2 capitalize px-2 md:px-4 py-2 font-semibold`;

    return (
        <div>
            <div className={`rounded-lg p-4 border border-n700 relative`}>
                {event.type_group !== "fire" && (
                    <EventPublicPrivate event={event} />
                )}
                <div className={`flex space-x-4 mt-2`}>
                    <EventTypeImage event={event} />
                    <div
                        className={`md:flex-1 md:flex md:flex-col md:justify-between`}
                    >
                        <EventTypeGroupIndicator event={event} />
                        <div>
                            <p className="text-white">
                                {event.name}{" "}
                                {event.eventType === EventTypeFires && (
                                    <span>Fire</span>
                                )}
                                {event.approved_at ? (
                                    <span
                                        className={`text-xxs bg-p200 text-white px-2 py-1 rounded-full ml-2`}
                                    >
                                        Published
                                    </span>
                                ) : (
                                    <span
                                        className={`text-xxs bg-grey-darker text-white px-2 py-1 rounded-full ml-2`}
                                    >
                                        Unpublished
                                    </span>
                                )}
                            </p>

                            <p className="text-sm text-white">
                                {event.location}
                            </p>
                        </div>
                        <div className={`flex items-center space-x-2 mt-2`}>
                            <div className="text-sm leading-tighter text-n40">
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
                        href={route("my.contacts", { event_id: event.id })}
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
