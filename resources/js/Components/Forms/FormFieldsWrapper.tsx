import React from "react";

export const FormFieldsWrapper = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return <div className="mt-2 space-y-4">{children}</div>;
};
