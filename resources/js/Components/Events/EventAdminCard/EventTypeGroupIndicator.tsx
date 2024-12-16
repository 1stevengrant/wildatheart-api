import React from "react";

import { useRecoilState } from "recoil";

import { EventTypeBadge } from "@/Components/Events/EventTypeBadge";
import { dateFormatter } from "@/helpers/dateFormatter";
import { EVENT_FOR_MEN } from "@/helpers/eventSex";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { currentEventState } from "@/store";
import { Event } from "@/types/Event";

export const EventTypeGroupIndicator = ({ event }: { event: Event }) => {
    const eventTypeClasses = `text-n40 font-semibold text-sm`;
    const eventTypeNameClasses = `flex items-center justify-between space-x-4`;

    const [currentEvent] = useRecoilState(currentEventState);
    const isActiveEvent =
        event.id === currentEvent?.id ? "text-p200" : "text-n40";
    return (
        <>
            {event.type_group === fireTypeGroup && (
                <div className={eventTypeNameClasses}>
                    <div className={`${eventTypeClasses} ${isActiveEvent}`}>
                        Monthly Gathering for Men
                    </div>
                    <EventTypeBadge className={`hidden md:inline-flex`}>
                        Monthly
                    </EventTypeBadge>
                </div>
            )}
            {event.type_group === retreatTypeGroup && (
                <div className={eventTypeNameClasses}>
                    <div className={`${eventTypeClasses} ${isActiveEvent}`}>
                        Multi-Day Retreat for{" "}
                        {event.sex === EVENT_FOR_MEN ? (
                            <span>Men</span>
                        ) : (
                            <span>Women</span>
                        )}
                    </div>
                    {event.start_date && (
                        <EventTypeBadge className={`hidden md:inline-flex`}>
                            {dateFormatter(event.start_date, "MMM Do")}
                            {event.end_date &&
                                ` - ${dateFormatter(event.end_date, "MMM Do YYYY")}`}
                        </EventTypeBadge>
                    )}
                </div>
            )}

            {event?.type_group === experienceTypeGroup && (
                <div className={eventTypeNameClasses}>
                    <div className={`${eventTypeClasses} ${isActiveEvent}`}>
                        6-part video study for{" "}
                        {event.sex === EVENT_FOR_MEN ? (
                            <span>Men</span>
                        ) : (
                            <span>Women</span>
                        )}
                    </div>
                    <EventTypeBadge className={`hidden md:inline-flex`}>
                        Starts {dateFormatter(event.start_date)}
                    </EventTypeBadge>
                </div>
            )}

            {event?.type_group === experienceTypeGroup && (
                <EventTypeBadge className={`my-2 inline-flex md:hidden`}>
                    Starts {dateFormatter(event.start_date)}
                </EventTypeBadge>
            )}

            {event?.type_group === fireTypeGroup && (
                <EventTypeBadge className={`my-2 inline-flex md:hidden`}>
                    Monthly
                </EventTypeBadge>
            )}
            {event?.type_group === retreatTypeGroup && (
                <EventTypeBadge className={`my-2 md:hidden`}>
                    Starts {dateFormatter(event.start_date)}
                </EventTypeBadge>
            )}
        </>
    );
};
