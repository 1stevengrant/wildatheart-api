import React from "react";

export const EmptyLayoutBody = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div
            className={`pb-[63px] md:pb-[93px] md:mx-auto overflow-y-scroll lg:h-screen ${className}`}
        >
            {children}
        </div>
    );
};
