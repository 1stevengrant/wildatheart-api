import React from 'react';

type EventLocationProps = {
    location: string | undefined;
};

const EventLocation: React.FC<EventLocationProps> = ({ location }) => {
    return (
        <p className="text-white font-semibold">{location}</p>
    );
};

export default EventLocation;
