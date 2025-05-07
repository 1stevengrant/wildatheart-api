import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";

export const EventCardContent = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            {event.event_type_image && (
                <Image
                    src={event.event_type_image}
                    className={`rounded-md flex-grow-0`}
                />
            )}
            <EventInfo>
                <div className={`leading-4`}>
                    <EventTypeGroupIndicator event={event} />
                    <EventLocation location={event.location} />
                </div>
                <EventHostInfo>
                    <>
                        {event.host_photo && (
                            <UserAvatar image={event.host_photo} />
                        )}
                    </>
                    <div className="text-n40 leading-4">
                        {event.type_group === fireTypeGroup && (
                            <div className={`text-sm capitalize`}>
                                {event.display_name}
                            </div>
                        )}
                        <HostedBy host={event.host_name} />
                    </div>
                </EventHostInfo>
            </EventInfo>
        </>
    );
};
