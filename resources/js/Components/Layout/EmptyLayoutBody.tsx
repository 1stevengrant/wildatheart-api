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
        <main
            className={`pt-[73px] md:pt-[93px] pb-[63px] md:pb-[93px] md:mx-auto ${className}`}
        >
            {children}
        </main>
    );
};
