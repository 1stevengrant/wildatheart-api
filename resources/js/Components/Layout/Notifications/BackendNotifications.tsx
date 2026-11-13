import { usePage } from "@inertiajs/react";

import { Notification } from "@/Components/Layout/Notifications/Notification";
import { NotificationWrapper } from "@/Components/Layout/Notifications/NotificationWrapper";

export const BackendNotifications = () => {
    const { notifications } = usePage().props;

    return (
        <>
            {notifications && (
                <NotificationWrapper>
                    <Notification message={notifications.message} />
                </NotificationWrapper>
            )}
        </>
    );
};
