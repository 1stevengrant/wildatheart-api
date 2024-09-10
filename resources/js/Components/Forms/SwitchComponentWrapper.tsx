import React from "react";

export const SwitchComponentWrapper = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div
            className={`my-2 flex items-center justify-between text-sm font-medium ${className}`}
        >
            {children}
        </div>
    );
};
