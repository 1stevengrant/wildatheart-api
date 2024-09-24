import { usePage } from "@inertiajs/react";
import { Event } from "@/types/Event";
import { EventType } from "@/types/EventType";

export const InertiaTypesFilter = () => {
    const { eventTypes } = usePage().props as any;
    const types = (
        eventTypes as {
            data: EventType[];
        }
    ).data;
    return (
        <div>
            {types.map((eventType: EventType) => {
                return (
                    <div key={eventType.id}>
                        <span>{eventType.name}</span>
                    </div>
                );
            })}
        </div>
    );
};
