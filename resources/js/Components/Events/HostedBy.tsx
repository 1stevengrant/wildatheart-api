import React from 'react';

type HostedByProps = {
    host: string | undefined
};

const HostedBy: React.FC<HostedByProps> = ({ host }) => {
    return (
        <div>
            Hosted by {host}
        </div>
    );
};

export default HostedBy;
