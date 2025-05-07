import React from "react";

export const FilterTypeWrapper = ({
    text = "",
    icon,
    title = "",
}: {
    text?: string;
    icon?: React.ReactNode;
    title?: string;
}) => {
    return (
        <>
            <div className={`flex items-center space-x-4`}>
                {icon}
                <div>{title}</div>
            </div>
            <p className={`text-sm text-n40 my-4`}>{text}</p>
        </>
    );
};
