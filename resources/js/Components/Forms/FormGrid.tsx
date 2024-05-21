import React from "react";

interface FormGridProps {
    children: React.ReactNode;
    className?: string;
}

export const FormGrid = ({ children, className }: FormGridProps) => {
    return (
        <div className={`md:grid md:grid-cols-2 md:gap-2 ${className}`}>
            {children}
        </div>
    );
};
