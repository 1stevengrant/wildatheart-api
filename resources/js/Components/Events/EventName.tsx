import React from "react";

type EventProps = {
    name: string | undefined;
};

const EventName: React.FC<EventProps> = ({ name }) => {
    return <p className="font-semibold text-white">{name} Fire</p>;
};

export default EventName;
