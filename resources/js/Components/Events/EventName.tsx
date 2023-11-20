import React from 'react';

type EventProps = {
    name: string | undefined;
};

const EventName: React.FC<EventProps> = ({ name }) => {
    return (
        <p className="text-white font-semibold">{name} Fire</p>
    );
};

export default EventName;
