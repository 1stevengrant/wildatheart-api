import React from "react";

import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { HostedBy } from "@/Components/Events/HostedBy";
import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { imageGenerator } from "@/helpers/user";
import { Event } from "@/types/Event";

export const EventCardContent = (props: { event: Event }) => {
    const { event } = props;
    return (
        <>
            {event.event_type_image && (
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
                    <EventLocation location={event.location} />
                </div>
                <EventHostInfo>
                    <>
                        {event.host_photo ? (
                            <img
                                src={event.host_photo}
                                className={`w-8 h-8 rounded-full object-cover`}
                                alt=""
                            />
                        ) : (
                            <FallbackAvatar
                                size={30}
                                name={event.host_full_name}
                                md5email={event.host_md5_email}
                            />
                        )}
                    </>
                    <div className="text-xs text-grey-200 leading-tighter">
                        {event.type_group === "Fires" && (
                            <div>{event.name} Fire</div>
                        )}
                        <HostedBy host={event.host_full_name} />
                    </div>
                </EventHostInfo>
            </EventInfo>
        </>
    );
};
