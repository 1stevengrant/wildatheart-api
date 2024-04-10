import React from "react";

const ModalHeading = ({ children }: { children: React.ReactNode }) => {
    return <h2 className={`text-2xl font-bold`}>{children}</h2>;
};

export default ModalHeading;
