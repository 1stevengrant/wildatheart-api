import React from "react";

type EmptyLayoutBodyProps = {
    children: React.ReactNode;
    className?: string;
};

export const EmptyLayoutBody = ({
    children,
    className = "",
}: EmptyLayoutBodyProps) => {
    return (
        <div
            className={`pb-[63px] md:pb-[93px] md:mx-auto overflow-y-scroll lg:h-screen ${className}`}
        >
            {children}
        </div>
    );
};
