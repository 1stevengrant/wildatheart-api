import React from "react";

type EmptyLayoutWrapperProps = {
    children: React.ReactNode;
};

export const EmptyLayoutWrapper = ({ children }: EmptyLayoutWrapperProps) => {
    return <div className={`flex flex-col h-full`}>{children}</div>;
};
