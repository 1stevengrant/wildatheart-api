import React from "react";

export const EventTypeBadge = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <span
            className={`rounded-full py-0.5 px-4 text-sm text-grey-200 border border-white flex-shrink-0 ${className}`}
        >
            {children}
        </span>
    );
};
