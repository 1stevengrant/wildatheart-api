import React from "react";
import { Marker } from "react-map-gl";
import { EventMarkerButton } from "@/Components/Events/EventMarkerButton";
import { useEventList } from "@/Hooks/useEventList";
import { usePopupInfo } from "@/Hooks/usePopupInfo";

export const EventList = () => {
    const { setPopupInfo } = usePopupInfo();
    const { data } = useEventList();

    return (
        <>
            {data.map((event) => (
                <Marker
                    key={event.id}
                    longitude={event.longitude}
                    latitude={event.latitude}
                >
                    {event.name}
                </Marker>
            ))}
        </>
    );
};
