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
            className={`container mx-auto px-6 py-20 prose prose-p:text-white prose-h1:text-white prose-li:text-white prose-ul:text-white prose-ol:text-white ${className} marker:text-white `}
        >
            {children}
        </div>
    );
};
