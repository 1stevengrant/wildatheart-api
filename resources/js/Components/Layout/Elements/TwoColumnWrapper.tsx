import React from "react";

import { usePage } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { Notification } from "@/Components/Layout/Notifications/Notification";
import { reactNotificationState } from "@/store";

interface TwoColumnWrapperProps {
    className?: string;
    children: React.ReactNode;
}

export const TwoColumnWrapper = ({
    className = "",
    children,
}: TwoColumnWrapperProps) => {
    const { notifications } = usePage<any>().props;
    const [reactNotifications] = useRecoilState(reactNotificationState);
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
            {notifications && <Notification message={notifications.message} />}
            {reactNotifications &&
                reactNotifications.map((notification, index) => (
                    <Notification
                        key={index}
                        message={notification.message}
                        type={notification.type}
                    />
                ))}
        </>
    );
};
