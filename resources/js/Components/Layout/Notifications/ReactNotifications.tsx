import React from "react";

import { useRecoilValue } from "recoil";

import { Notification } from "@/Components/Layout/Notifications/Notification";
import { NotificationWrapper } from "@/Components/Layout/Notifications/NotificationWrapper";
import { reactNotificationState } from "@/store";

export const ReactNotifications = () => {
    const reactNotifications = useRecoilValue(reactNotificationState);

    if (!reactNotifications.length) return null;

    return (
        <NotificationWrapper>
            {reactNotifications.map((notification, index) => (
                <Notification
                    key={index}
                    message={notification.message}
                    type={notification.type}
                />
            ))}
        </NotificationWrapper>
    );
};
