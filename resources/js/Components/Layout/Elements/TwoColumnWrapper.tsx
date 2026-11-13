import React from "react";

import { Notifications } from "@/Components/Layout/Notifications/Notifications";

export const TwoColumnWrapper = ({
    className = "",
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) => {
    return (
        <>
            <div
                className={
                    className +
                    " md:flex space-x-8 min-h-[100dvh] md:min-h-auto md:h-screen-[100dvh] px-4" +
                    " py-4"
                }
            >
                {children}
            </div>
            <Notifications />
        </>
    );
};
