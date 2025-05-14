import React from "react";

export const EmptyLayoutFooter = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return (
        <footer
            className={`bg-black/50 backdrop-blur-sm text-white text-center p-4 fixed bottom-0 inset-x-0 z-[10000] flex items-center justify-between lg:pl-72`}
        >
            <div
                className={`w-full md:w-[680px] md:mx-auto flex items-center justify-between`}
            >
                {children}
            </div>
        </footer>
    );
};
