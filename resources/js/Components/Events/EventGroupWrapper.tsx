import React from "react";

type EventGroupWrapperProps = {
    children: React.ReactNode;
    title: string;
    description: string;
};

export const EventGroupWrapper = ({
    children,
    title,
    description,
}: EventGroupWrapperProps) => {
    return (
        <div>
            <h2 className={`font-bold text-lg`}>{title}</h2>
            <p className={`text-n40 text-sm mb-4`}>{description}</p>
            {children}
        </div>
    );
};
