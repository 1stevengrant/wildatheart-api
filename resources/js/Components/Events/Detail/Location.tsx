import React from "react";

import { MapPinIcon } from "@heroicons/react/24/outline";

interface LocationProps {
    location?: string;
    children?: React.ReactNode;
    className?: string;
}

export const Location = ({
    location,
    children,
    className = "",
}: LocationProps) => {
    return (
        <div className={`flex items-center space-x-1 ${className}`}>
            <MapPinIcon className={`w-6 h-6`} />
            <h2 className="leading-tighter">
                {location} {children}
            </h2>
        </div>
    );
};
