import {
    EventTypeBecomingAKingExperience,
    EventTypeBecomingAKingRetreat,
    EventTypeCaptivatingExperience,
    EventTypeCaptivatingRetreat,
    EventTypeFires,
    EventTypeWahExperience,
    EventTypeWahRetreat,
} from "@/helpers/eventNames";

export const eventTypeBackground = (eventTypeSlug: string) => {
    switch (eventTypeSlug) {
        case EventTypeFires:
            return "from-amber-700";
        case EventTypeCaptivatingRetreat:
            return "from-pink-500";
        case EventTypeCaptivatingExperience:
            return "from-green-400";
        case EventTypeWahRetreat:
            return "from-[#C1440E]";
        case EventTypeBecomingAKingRetreat:
            return "from-green-400";
        case EventTypeWahExperience:
            return "from-green-400";
        case EventTypeBecomingAKingExperience:
            return "from-green-400";
        default:
            return "from-amber-700";
    }
};
