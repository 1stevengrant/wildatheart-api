import React from "react";

import { EventCreateProgress } from "@/Components/Layout/EventCreateProgress";

type EmptyLayoutHeaderProps = {
    children: React.ReactNode;
};
export const EmptyLayoutHeader = ({ children }: EmptyLayoutHeaderProps) => {
    return (
        <header
            className={`bg-black text-white text-center p-4 fixed top-0 inset-x-0 z-10 border-b border-grey-border`}
        >
            {children}
        </header>
    );
};
