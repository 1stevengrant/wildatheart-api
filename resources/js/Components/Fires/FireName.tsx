import React from 'react';

type FireProps = {
    name: string;
};

const FireName: React.FC<FireProps> = ({ name }) => {
    return (
        <p className="text-white font-semibold">{name} Fire</p>
    );
};

export default FireName;
