import React from "react";

type EventProps = {
    name: string | undefined;
};

const EventName = ({ name }: EventProps) => {
    return <p className="font-semibold text-white">{name} Fire</p>;
};

export default EventName;
