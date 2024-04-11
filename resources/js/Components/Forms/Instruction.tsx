import React from "react";

interface InstructionProps {
    children: React.ReactNode;
}

export const Instruction: React.FC<InstructionProps> = ({ children }) => {
    return <div className={"text-sm text-grey-200 mt-4"}>{children}</div>;
};
