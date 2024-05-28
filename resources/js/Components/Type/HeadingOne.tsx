import React from "react";

export const HeadingOne = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <h1
            className={`text-xl md:text-3xl leading-tight md:leading-normal text-white ${className}`}
        >
            {children}
        </h1>
    );
};
