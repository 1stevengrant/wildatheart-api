import {
    EventTypeCaptivatingFires,
    EventTypeFires,
} from "@/helpers/eventNames";

export const getRouteForEventType = (slug: string) => {
    switch (slug) {
        case EventTypeFires:
            return route("host.fire");
        case EventTypeCaptivatingFires:
            return route("host.captivating-fire");
        default:
            return route("host.eventType.index", slug);
    }
};
