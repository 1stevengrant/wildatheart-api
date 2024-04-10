import React from "react";

interface RightColumnProps {
    className?: string;
    children: React.ReactNode;
}

const RightColumn = ({ className, children }: RightColumnProps) => {
    return (
        <div
            className={className + " md:w-1/2 md:flex flex-col justify-center"}
        >
            {children}
        </div>
    );
};

export default RightColumn;
