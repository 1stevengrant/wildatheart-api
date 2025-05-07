import React from "react";

export const EmptyLayoutHeader = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return (
        <header
            className={`bg-black text-white text-center p-4 fixed top-0 inset-x-0 z-10 border-b border-n700 md:hidden`}
        >
            <div className={`md:w-[680px] md:mx-auto`}>{children}</div>
        </header>
    );
};
