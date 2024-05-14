import React from "react";

type EventLocationProps = {
    location: string | undefined;
};

const EventLocation = ({ location }: EventLocationProps) => {
    return <p className="font-semibold text-white">{location}</p>;
};

export default EventLocation;
