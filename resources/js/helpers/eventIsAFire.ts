import {
    EventTypeFires,
    EventTypeCaptivatingFires,
} from "@/helpers/eventNames";

export const eventIsAFire = (eventType: string) => {
    return (
        eventType === EventTypeFires || eventType === EventTypeCaptivatingFires
    );
};
