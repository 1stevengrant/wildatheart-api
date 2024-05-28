import React from "react";
export const EventInfo = ({ children }: {
    children: React.ReactNode;
}) => {
    return (
        <div className={"flex flex-col flex-1 justify-between relative"}>
            {children}
        </div>
    );
};
