import React from 'react';

type FireLocationProps = {
    location: string;
};

const FireLocation: React.FC<FireLocationProps> = ({ location }) => {
    return (
        <p className="text-white font-semibold">{location}</p>
    );
};

export default FireLocation;
