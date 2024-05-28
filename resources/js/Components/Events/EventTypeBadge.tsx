import React from "react";

export const EventTypeBadge = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div
            className={`rounded-full py-0.5 px-4 text-sm text-grey-200 border border-white ${className}`}
        >
            {children}
        </div>
    );
};
