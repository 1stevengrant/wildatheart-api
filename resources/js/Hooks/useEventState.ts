import { useState } from "react";

interface UseEventStateProps {
    user: any;
    eventType?: any;
    initialEvent?: any;
}

export const useEventState = ({
    user,
    eventType,
    initialEvent,
}: UseEventStateProps) => {
    const [event, setEvent] = useState({
        name: initialEvent?.name || `${user.given_name}'s`,
        location: initialEvent?.location || "Location TBC",
        host: user.fullname,
        host_photo: user.image,
        country: initialEvent?.country || null,
        region: initialEvent?.region || null,
        eventType: eventType,
        hostEmail: user.email,
        frequency: initialEvent?.frequency || "monthly",
        start_date: initialEvent?.start_date || null,
        ...initialEvent,
        day_count: initialEvent?.day_count || null,
    });

    const updateEvent = (updates: Partial<typeof event>) => {
        setEvent((prev: any) => ({ ...prev, ...updates }));
    };

    const updateEventFromLocation = (locationData: any) => {
        updateEvent({
            location: locationData.city,
            country: locationData.country,
            region: locationData.region,
        });
    };

    return {
        event,
        setEvent,
        updateEvent,
        updateEventFromLocation,
    };
};
