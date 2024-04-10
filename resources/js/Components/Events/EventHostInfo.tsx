import React from "react";
type EventHostInfoProps = {
    children: React.ReactNode;
};
const EventHostInfo = ({ children }: EventHostInfoProps) => {
    return <div className={"flex items-center space-x-2"}>{children}</div>;
};

export default EventHostInfo;
