import { Event } from "@/types/Event";

export const isSelectedEvent = (
    event: Event,
    currentEvent: Event | undefined | null,
    borderColor?: string
) => {
    return event.id === currentEvent?.id
        ? "border-p200"
        : `${borderColor ? `${borderColor}` : `border-transparent`}`;
};
