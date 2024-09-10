import React from "react";

import { CalendarDaysIcon } from "@heroicons/react/24/outline";

export const Date = ({
    date,
    children,
    className,
}: {
    date?: string;
    children?: React.ReactNode;
    className?: string;
}) => {
    return (
        <div className={`flex items-center space-x-1 ${className}`}>
            <CalendarDaysIcon className={`w-6 h-6`} />
            <h2 className="leading-tighter">
                {date} {children}
            </h2>
        </div>
    );
};
