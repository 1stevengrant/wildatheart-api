import { EventTypeFires } from "@/helpers/eventNames";

export const eventIsAFire = (event: string) => {
    return event === EventTypeFires;
};
