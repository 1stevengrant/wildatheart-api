import React from "react";

export const FormGrid = ({
    children,
    className,
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div className={`md:grid md:grid-cols-2 md:gap-2 ${className}`}>
            {children}
        </div>
    );
};
