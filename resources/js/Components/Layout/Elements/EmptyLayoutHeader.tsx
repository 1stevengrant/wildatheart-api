import React from "react";

type EmptyLayoutHeaderProps = {
    children: React.ReactNode;
};
export const EmptyLayoutHeader = ({ children }: EmptyLayoutHeaderProps) => {
    return (
        <header
            className={`bg-black text-white text-center p-4 fixed top-0 inset-x-0 z-10 border-b border-n700 md:hidden`}
        >
            <div className={`md:w-[680px] md:mx-auto`}>{children}</div>
        </header>
    );
};
