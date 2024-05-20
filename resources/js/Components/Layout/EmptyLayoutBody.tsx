import React from "react";

type EmptyLayoutBodyProps = {
    children: React.ReactNode;
    className?: string;
};

export const EmptyLayoutBody = ({
    children,
    className = "",
}: EmptyLayoutBodyProps) => {
    return (
        <main className={`pt-[73px] pb-[63px] ${className}`}>{children}</main>
    );
};
