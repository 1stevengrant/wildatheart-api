import React from "react";

interface InstructionProps {
    children: React.ReactNode;
}

const Instruction: React.FC<InstructionProps> = ({ children }) => {
    return <div className={"text-sm text-grey-200 mt-4"}>{children}</div>;
};

export default Instruction;
