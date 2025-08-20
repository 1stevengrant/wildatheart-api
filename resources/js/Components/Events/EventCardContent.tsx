import { EventTypeGroupIndicator } from "@/Components/Events/EventAdminCard/EventTypeGroupIndicator";
import { EventHostInfo } from "@/Components/Events/EventHostInfo";
import { EventInfo } from "@/Components/Events/EventInfo";
import { EventLocation } from "@/Components/Events/EventLocation";
import { HostedBy } from "@/Components/Events/HostedBy";
import { Image } from "@/Components/Image";
import { UserAvatar } from "@/Components/UserAvatar";
import { fireTypeGroup } from "@/helpers/eventTypeGroups";
import { FeaturedBadge } from "@/Components/Events/FeaturedBadge";

export const EventCardContent = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            {event.event_type_image && (
                <div className="space-y-2">
                    <Image
                        src={event.event_type_image}
                        className={`rounded-md flex-grow-0`}
                    />
                    <FeaturedBadge event={event} />
                </div>
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
                            <div className={`capitalize`}>
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
