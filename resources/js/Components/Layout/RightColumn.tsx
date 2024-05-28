import React from "react";

export const RightColumn = ({
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
