import React from "react";

interface FormFieldsWrapperProps {
    children: React.ReactNode;
}

const FormFieldsWrapper: React.FC<FormFieldsWrapperProps> = ({ children }) => {
    return <div className="mt-2 space-y-4">{children}</div>;
};

export default FormFieldsWrapper;
