import React from "react";
interface EventTypeBadgeProps {
    children: React.ReactNode;
    className?: string;
}
const EventTypeBadge = ({ children, className = "" }: EventTypeBadgeProps) => {
    return (
        <div
            className={`rounded-full py-0.5 px-4 text-sm text-grey-200 border border-white ${className}`}
        >
            {children}
        </div>
    );
};

export default EventTypeBadge;
