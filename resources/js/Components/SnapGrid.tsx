import React from "react";

type SnapGridProps = {
    children: React.ReactNode;
};
export const SnapGrid = ({ children }: SnapGridProps) => {
    return (
        <div
            className={`overflow-x-auto snap-mandatory snap-x grid gap-4 md:gap-6 px-1 py-3 card-grid`}
        >
            {children}
        </div>
    );
};
