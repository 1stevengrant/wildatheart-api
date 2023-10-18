import React from 'react';

type HostedByProps = {
    host: string;
};

const HostedBy: React.FC<HostedByProps> = ({ host }) => {
    return (
        <div className="text-grey-200 text-xs leading-tighter">
            Hosted by {host}
        </div>
    );
};

export default HostedBy;
