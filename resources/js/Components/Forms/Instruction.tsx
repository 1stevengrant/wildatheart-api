import React from "react";

export const Instruction = ({ children }: { children: React.ReactNode }) => {
    return <div className={"text-sm text-grey-200 mt-4"}>{children}</div>;
};
