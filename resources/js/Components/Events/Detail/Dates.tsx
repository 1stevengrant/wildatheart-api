import React, { FC } from "react";

import { CalendarDaysIcon } from "@heroicons/react/24/outline";

interface DateProps {
    date?: string;
    children?: React.ReactNode;
    className?: string;
}

const Date: FC<DateProps> = ({ date, children, className }) => {
    return (
        <div className={`flex items-center space-x-1 ${className}`}>
            <CalendarDaysIcon className={`w-6 h-6`} />
            <h2 className="leading-tighter">
                {date} {children}
            </h2>
        </div>
    );
};

export default Date;
