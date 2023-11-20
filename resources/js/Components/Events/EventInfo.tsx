import React from "react";
type EventHostInfoProps = {
    children: React.ReactNode;
}
const EventInfo = ({children}: EventHostInfoProps) => {
    return (
        <div className={'flex flex-col justify-between relative'}>
            {children}
        </div>
    )
}

export default EventInfo
