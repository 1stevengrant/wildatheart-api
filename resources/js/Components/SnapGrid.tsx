import React from "react";

export const SnapGrid = ({ children }: { children: React.ReactNode }) => {
    return (
        <div
            className={`overflow-x-auto snap-mandatory snap-x grid gap-4 md:gap-6 px-1 py-3 card-grid`}
        >
            {children}
        </div>
    );
};
