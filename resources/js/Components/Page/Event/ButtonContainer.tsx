import React from "react";

type ButtonContainerProps = {
    children: React.ReactNode;
};

export const ButtonContainer = ({ children }: ButtonContainerProps) => {
    return (
        <div className={`flex items-center justify-between`}>{children}</div>
    );
};
