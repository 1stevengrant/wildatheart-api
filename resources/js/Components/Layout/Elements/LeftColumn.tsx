import React from "react";

export const LeftColumn = ({
    className = "",
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) => {
    return (
        <div
            className={className + " md:w-1/2 md:flex flex-col justify-center"}
        >
            {children}
        </div>
    );
};
