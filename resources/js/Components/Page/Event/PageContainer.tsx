import React from "react";

type PageContainerProps = {
    children: React.ReactNode;
    className?: string;
};
export const PageContainer = ({
    children,
    className = "",
}: PageContainerProps) => {
    return (
        <div
            className={`container mx-auto py-20 prose prose-p:text-white prose-h1:text-white ${className}`}
        >
            {children}
        </div>
    );
};
