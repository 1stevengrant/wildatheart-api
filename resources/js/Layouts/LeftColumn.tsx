import React from "react";

import { LeftColumnProps } from "@/types/LeftColumnProps";

export const LeftColumn = ({ className, children }: LeftColumnProps) => {
    return (
        <div
            className={className + " md:w-1/2 md:flex flex-col justify-center"}
        >
            {children}
        </div>
    );
};
