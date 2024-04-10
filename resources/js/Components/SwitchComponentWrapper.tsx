import React from "react";

type SwitchComponentWrapperProps = {
    children: React.ReactNode;
    className?: string;
};

export const SwitchComponentWrapper = ({
    children,
    className = "",
}: SwitchComponentWrapperProps) => {
    return (
        <div
            className={`my-2 flex items-center justify-between text-sm font-medium ${className}`}
        >
            {children}
        </div>
    );
};
