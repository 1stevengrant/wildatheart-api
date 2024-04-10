import React from "react";

import { UserCircleIcon } from "@heroicons/react/24/outline";
import { useRecoilState } from "recoil";

import EventHostInfo from "@/Components/Events/EventHostInfo";
import EventInfo from "@/Components/Events/EventInfo";
import EventLocation from "@/Components/Events/EventLocation";
import EventTypeBadge from "@/Components/Events/EventTypeBadge";
import { HostedBy } from "@/Components/Events/HostedBy";
import UserAvatar from "@/Components/UserAvatar";
import { dateFormatter } from "@/helpers/dateFormatter";
import { EVENT_FOR_MEN } from "@/helpers/eventSex";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { imageGenerator } from "@/helpers/user";
import { currentEventState } from "@/store";
import { IEventCardContentProps } from "@/types/IEventCardContentProps";

export const EventCardContent = (props: IEventCardContentProps) => {
    const eventTypeClasses = `text-grey-200 font-semibold text-sm`;
    const eventTypeNameClasses = `flex items-center justify-between space-x-4`;
    const { event } = props;
    const [currentEvent] = useRecoilState(currentEventState);
    const isActiveEvent =
        event?.id === currentEvent?.id ? "text-orange" : "text-grey-200";

    return (
        <>
            {event?.event_type_image && (
                <img
                    src={imageGenerator(event.event_type_image, {
                        width: 100,
                        height: 100,
                    })}
                    className={`rounded-md flex-grow-0`}
                    alt=""
                />
            )}
            <EventInfo>
                <div>
                    {event?.type_group === fireTypeGroup && (
                        <div className={eventTypeNameClasses}>
                            <div
                                className={`${eventTypeClasses} ${isActiveEvent}`}
                            >
                                Monthly Gathering for Men
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                Monthly
                            </EventTypeBadge>
                        </div>
                    )}
                    {event?.event_type === retreatTypeGroup && (
                        <div className={eventTypeNameClasses}>
                            <div
                                className={`${eventTypeClasses} ${isActiveEvent}`}
                            >
                                Multi-Day retreat for{" "}
                                {event.sex === EVENT_FOR_MEN ? (
                                    <span>Men</span>
                                ) : (
                                    <span>Women</span>
                                )}
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                {dateFormatter(event.start_date, "MMM D")} -{" "}
                                {dateFormatter(event.end_date, "MMM D")}
                            </EventTypeBadge>
                        </div>
                    )}
                    {event?.type_group === retreatTypeGroup && (
                        <EventTypeBadge
                            className={`my-2 inline-flex md:hidden`}
                        >
                            Starts {dateFormatter(event.start_date, "MMM D")}
                        </EventTypeBadge>
                    )}
                    {event?.type_group === experienceTypeGroup && (
                        <div className={eventTypeNameClasses}>
                            <div
                                className={`${eventTypeClasses} ${isActiveEvent}`}
                            >
                                6-part video study for{" "}
                                {event.sex === EVENT_FOR_MEN ? (
                                    <span>Men</span>
                                ) : (
                                    <span>Women</span>
                                )}
                            </div>
                            <EventTypeBadge className={`hidden md:inline-flex`}>
                                Starts{" "}
                                {dateFormatter(event.start_date, "MMM D")}
                            </EventTypeBadge>
                        </div>
                    )}

                    {event?.type_group === experienceTypeGroup && (
                        <EventTypeBadge
                            className={`my-2 inline-flex md:hidden`}
                        >
                            Starts {dateFormatter(event.start_date, "MMM D")}
                        </EventTypeBadge>
                    )}

                    <EventLocation location={event?.location} />

                    {event?.type_group === fireTypeGroup && (
                        <EventTypeBadge
                            className={`my-2 inline-flex md:hidden`}
                        >
                            Monthly
                        </EventTypeBadge>
                    )}
                </div>
                <EventHostInfo>
                    <>
                        {event?.host_photo ? (
                            <UserAvatar image={event.host_photo} width={32} />
                        ) : (
                            <UserCircleIcon className={"w-6 h-6 text-white"} />
                        )}
                    </>
                    <div className="text-xs text-grey-200 leading-tighter">
                        {event?.type_group === fireTypeGroup && (
                            <div>{event.name} Fire</div>
                        )}
                        <HostedBy host={event?.host_name} />
                    </div>
                </EventHostInfo>
            </EventInfo>
        </>
    );
};
