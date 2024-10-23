import React from "react";

import { useRecoilValue } from "recoil";

import { Notification } from "@/Components/Layout/Notifications/Notification";
import { NotificationWrapper } from "@/Components/Layout/Notifications/NotificationWrapper";
import { reactNotificationState } from "@/store";

export const ReactNotifications = () => {
    const notifications = useRecoilValue(reactNotificationState);

    if (notifications.length === 0) {
        return null;
    }

    return (
        <NotificationWrapper>
            {notifications.map((notification, index) => (
                <Notification
                    key={`notification-${notification.id || index}`}
                    message={notification.message}
                    type={notification.type}
                />
            ))}
        </NotificationWrapper>
    );
};
