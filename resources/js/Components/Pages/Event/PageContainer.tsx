import React from "react";

export const PageContainer = ({
    children,
    className = "",
}: {
    children: React.ReactNode;
    className?: string;
}) => {
    return (
        <div
            className={`container mx-auto px-6 prose prose-p:text-white prose-h1:text-white prose-h2:text-white prose-h3:text-white prose-li:text-white prose-ul:text-white prose-ol:text-white marker:text-white prose-a:text-p200 prose-h1:font-medium prose-h2:font-medium prose-h3:font-medium ${className}`}
        >
            {children}
        </div>
    );
};
