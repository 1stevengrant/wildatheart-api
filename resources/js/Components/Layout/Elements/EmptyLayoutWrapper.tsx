import React from "react";

export const EmptyLayoutWrapper = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return <div className={`flex flex-col h-full`}>{children}</div>;
};
