import React from "react";

interface FireComponentWrapperProps {
    children: React.ReactNode;
    className?: string;
    onClick?: () => void;
}

const FireComponentWrapper = ({children, className, onClick}:FireComponentWrapperProps) => {
    return (
        <div className={className + ' p-4 flex space-x-4 border rounded-md cursor-pointer'}>
            {children}
        </div>
    );
};

export default FireComponentWrapper;
