import React from "react";

interface FormGridProps {
    children: React.ReactNode;
    className?: string;
}

const FormGrid = ({children, className}: FormGridProps) => {
    return (
        <div className={`md:grid space-y-4 md:space-y-0 md:grid-cols-2 md:gap-2 ${className}`}>
            {children}
        </div>
    )
}

export default FormGrid;
