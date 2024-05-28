import React from "react";

export const CenterContainer = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return (
        <div
            className={`flex flex-col justify-center items-center flex-1 lg:h-[100dvh] p-6`}
        >
            {children}
        </div>
    );
};
