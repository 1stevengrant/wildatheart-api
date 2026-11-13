import { usePage } from "@inertiajs/react";
import { useRecoilValue } from "recoil";

import { Notification } from "@/Components/Layout/Notifications/Notification";
import { NotificationWrapper } from "@/Components/Layout/Notifications/NotificationWrapper";
import { reactNotificationState } from "@/store";

export const Notifications = () => {
    const { notifications } = usePage().props;
    const reactNotifications = useRecoilValue(reactNotificationState);

    if (!notifications && reactNotifications.length === 0) {
        return null;
    }

    return (
        <NotificationWrapper>
            {notifications && (
                <Notification
                    message={notifications.message}
                    type={notifications.type}
                />
            )}
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
