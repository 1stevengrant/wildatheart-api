import {
    EventTypeBecomingAKingExperience,
    EventTypeBecomingAKingRetreat,
    EventTypeCaptivatingExperience,
    EventTypeCaptivatingFires,
    EventTypeCaptivatingRetreat,
    EventTypeFires,
    EventTypeWahExperience,
    EventTypeWahRetreat,
} from "@/helpers/eventNames";

export const eventTypeBackground = (eventTypeSlug: string) => {
    switch (eventTypeSlug) {
        case EventTypeFires:
            return "from-[#8E3920]";
        case EventTypeCaptivatingFires:
            return "from-[#8E3920]";
        case EventTypeCaptivatingRetreat:
            return "from-[#73919C]";
        case EventTypeCaptivatingExperience:
            return "from-[#73919C]";
        case EventTypeWahRetreat:
            return "from-[#8E3920]";
        case EventTypeBecomingAKingRetreat:
            return "from-[#343628]";
        case EventTypeWahExperience:
            return "from-[#2C2024]";
        case EventTypeBecomingAKingExperience:
            return "from-[#343628]";
        default:
            return "from-amber-700";
    }
};
