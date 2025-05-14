import React from "react";

export const NotificationWrapper = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return (
        <div
            className={`fixed top-4 mx-6 mb-4 right-4 flex space-y-4 flex-col min-w-[300px] z-50`}
        >
            {children}
        </div>
    );
};
