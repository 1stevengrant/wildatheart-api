import React from "react";

type EmptyLayoutFooterProps = {
    children: React.ReactNode;
};
export const EmptyLayoutFooter = ({ children }: EmptyLayoutFooterProps) => {
    return (
        <footer
            className={`bg-black text-white text-center p-4 fixed bottom-0 inset-x-0 z-[10000] flex items-center justify-between lg:pl-72`}
        >
            <div
                className={`w-full md:w-[680px] md:mx-auto flex items-center justify-between`}
            >
                {children}
            </div>
        </footer>
    );
};
