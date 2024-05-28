import React from "react";

interface NotificationWrapperProps {
    children: React.ReactNode;
}

export const NotificationWrapper = ({ children }: NotificationWrapperProps) => {
    return (
        <div
            className={`fixed top-4 mx-6 mb-4 right-4 flex space-y-4 flex-col min-w-[300px] z-[49]`}
        >
            {children}
        </div>
    );
};
