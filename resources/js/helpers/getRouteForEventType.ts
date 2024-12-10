import {
    EventTypeCaptivatingFires,
    EventTypeFires,
} from "@/helpers/eventNames";
import { EventType } from "@/types/EventType";

export const getRouteForEventType = (eventType: EventType) => {
    switch (eventType.slug) {
        case EventTypeFires:
            return route("host.fire");
        case EventTypeCaptivatingFires:
            return route("host.captivating-fire");
        default:
            return route("host.eventType.index", eventType.slug);
    }
};
