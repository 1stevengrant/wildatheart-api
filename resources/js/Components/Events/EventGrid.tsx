import React from "react";

export const EventGrid = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div className={`grid gap-6 md:grid-cols-2 clear-both ${className}`}>
            {children}
        </div>
    );
};
