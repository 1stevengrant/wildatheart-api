import { useRecoilState } from "recoil";

import { EventTypeBadge } from "@/Components/Events/EventTypeBadge";
import { currentEventState } from "@/store/eventsAtom";

export const EventTypeGroupIndicator = ({
    event,
}: {
    event: App.Data.EventData;
}) => {
    const [currentEvent] = useRecoilState(currentEventState);
    const isActive = event.id === currentEvent?.id;

    if (!event.type_group) return null;

    return (
        <>
            <div className={`flex items-center justify-between space-x-4`}>
                <div className={`${isActive ? "text-p200" : "text-n40"}`}>
                    <span className={`text-sm`}>{event.card_title}</span>
                </div>
                <EventTypeBadge className={`hidden md:block`}>
                    {event.badge_display}
                </EventTypeBadge>
            </div>
            <EventTypeBadge className="my-2 inline-flex md:hidden">
                {event.badge_display}
            </EventTypeBadge>
        </>
    );
};
