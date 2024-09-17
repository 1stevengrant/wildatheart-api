import { Event } from "@/types/Event";

export interface IStaticMapProps {
    lat: number;
    lng: number;
    event: Event | null;
    className?: string;
}
