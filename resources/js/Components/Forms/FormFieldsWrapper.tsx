import React from "react";

interface FormFieldsWrapperProps {
    children: React.ReactNode;
}

export const FormFieldsWrapper = ({ children }: FormFieldsWrapperProps) => {
    return <div className="mt-2 space-y-4">{children}</div>;
};
