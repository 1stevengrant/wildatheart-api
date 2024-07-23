import React from "react";

export const Instruction = ({
    children,
    className = "mt-4",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div className={`text-sm text-grey-200 ${className} `}>{children}</div>
    );
};
