import React from "react";

type EventLocationProps = {
    location: string | undefined;
};

const EventLocation: React.FC<EventLocationProps> = ({ location }) => {
    return <p className="font-semibold text-white">{location}</p>;
};

export default EventLocation;
