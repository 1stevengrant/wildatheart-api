import React from "react";

interface InstructionProps {
    children: React.ReactNode;
}

export const Instruction = ({ children }: InstructionProps) => {
    return <div className={"text-sm text-grey-200 mt-4"}>{children}</div>;
};
