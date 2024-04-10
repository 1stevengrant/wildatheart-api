import React from "react";

type HostedByProps = {
    host: string | undefined;
};

export const HostedBy = ({ host }: HostedByProps) => {
    return <div>Hosted by {host}</div>;
};
