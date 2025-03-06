import React, { useEffect } from "react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { HostedBy } from "@/Components/Events/HostedBy";
import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { Event } from "@/types/Event";

export const EventDisplay = ({
    event,
    className = "",
}: {
    event: Event;
    className?: string;
}) => {
    const page = useTypedPage();
    const { auth } = page.props;

    useEffect(() => {}, [event]);

    return (
        <div
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
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
                <div className={`leading-tight`}>
                    <EventName name={event.name} />
                    <EventLocation location={event.location} />
                </div>
                <EventHostInfo>
                    <AuthUserAvatar auth={auth} />
                    <HostedBy host={auth.user?.fullname} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};
