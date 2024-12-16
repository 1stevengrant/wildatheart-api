import React, { useEffect } from "react";

import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { EventName } from "@/Components/Events/EventName";
import { HostedBy } from "@/Components/Events/HostedBy";
import { FallbackAvatar } from "@/Components/FallbackAvatar";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";

export const EventDisplay = ({
    event,
    className = "",
}: {
    event: any;
    className?: string;
}) => {
    const page = useTypedPage();
    const { auth } = page.props;

    useEffect(() => {}, [event]);

    return (
        <div
            className={`${className} p-4 flex space-x-4 border rounded-md cursor-pointer border-n700`}
        >
            {event.eventType.image && (
                <img
                    src={imageGenerator(event.eventType.image, {
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
                    {auth.user && (
                        <FallbackAvatar
                            name={event.host}
                            md5email={auth.user.md5email}
                            size={24}
                        />
                    )}

                    <HostedBy host={event.host} />
                </EventHostInfo>
            </EventInfo>
        </div>
    );
};
