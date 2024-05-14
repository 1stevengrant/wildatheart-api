import React from "react";

type CenterContainerProps = {
    children: React.ReactNode;
};
export const CenterContainer = ({ children }: CenterContainerProps) => {
    return (
        <div
            className={`flex flex-col justify-center items-center flex-1 lg:h-[100dvh] p-6`}
        >
            {children}
        </div>
    );
};
