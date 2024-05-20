import React from "react";

type EmptyLayoutFooterProps = {
    children: React.ReactNode;
};
export const EmptyLayoutFooter = ({ children }: EmptyLayoutFooterProps) => {
    return (
        <footer
            className={`border-t border-grey-border bg-black text-white text-center p-4 fixed bottom-0 inset-x-0 z-10 flex items-center justify-between lg:max-w-7xl lg:mx-auto`}
        >
            {children}
        </footer>
    );
};
