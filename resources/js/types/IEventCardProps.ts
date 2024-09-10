import { Event } from "@/types/Event";

export type IEventCardProps = {
    event: Event;
    className?: string;
    onClick?: (event: Event) => void;
    onMouseEnter?: (event: any) => void;
    onMouseLeave?: (event: any) => void;
};
