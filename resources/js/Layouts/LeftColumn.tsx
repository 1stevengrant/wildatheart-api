import React from "react";

interface LeftColumnProps {
    className?: string;
    children: React.ReactNode;
}

const LeftColumn = ({className, children}: LeftColumnProps) => {
    return (
        <div className={className + ' md:w-1/2 md:flex flex-col justify-center'}>
            {children}
        </div>
    );
}

export default LeftColumn;
