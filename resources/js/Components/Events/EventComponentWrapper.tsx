import React from "react";

interface EventComponentWrapperProps {
    children: React.ReactNode;
    className?: string;
    onClick?: () => void;
}

const EventComponentWrapper = ({children, className}:EventComponentWrapperProps) => {
    return (
        <div className={className + ' p-4 flex space-x-4 border rounded-md cursor-pointer'}>
            {children}
        </div>
    );
};

export default EventComponentWrapper;
