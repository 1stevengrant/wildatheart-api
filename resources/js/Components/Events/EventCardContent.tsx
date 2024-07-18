import React from "react";

import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { HostedBy } from "@/Components/Events/HostedBy";
import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { UserAvatar } from "@/Components/UserAvatar";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";
import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const EventCardContent = (props: { event: Event }) => {
    const { event } = props;
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
                    <EventTypeGroupIndicator event={event} />
                    <EventLocation location={event?.location} />
                </div>
                <EventHostInfo>
                    <>
                        {event?.host_photo ? (
                            <UserAvatar image={event.host_photo} />
                        ) : (
                            <FallbackAvatar
                                size={30}
                                name={event.hostObject.full_name}
                                md5email={event.hostObject.md5email}
                            />
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
