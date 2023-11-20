import React from "react";
import { GeoHit } from "@/types/EventHit";
import EventContent from "./EventContent";

interface EventComponentProps {
    event: GeoHit;
    onClick: (event: GeoHit) => void;
    currentEvent: GeoHit | null;
}

const EventComponent: React.FC<EventComponentProps> = ({ event, onClick, currentEvent }) => {
    const isSelected = currentEvent?.objectID === event.objectID;

    return (
        <div
            className={`p-4 flex space-x-4 border rounded-md cursor-pointer ${
                isSelected ? "border-orange border-2" : "border-grey-border"
            }`}
            onClick={() => onClick(event)}
        >
            <EventContent event={event} />
        </div>
    );
};

export default EventComponent;
