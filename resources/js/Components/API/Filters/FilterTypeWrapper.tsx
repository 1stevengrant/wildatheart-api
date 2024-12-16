import React from "react";

import { IFilterTypeWrapperProps } from "@/types/IFilterTypeWrapperProps";

export const FilterTypeWrapper = ({
    text = "",
    icon,
    title = "",
}: IFilterTypeWrapperProps) => {
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
