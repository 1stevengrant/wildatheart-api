import React from "react";

import { usePage } from "@inertiajs/react";

import { Notification } from "@/Components/Layout/Notification";
import { NotificationWrapper } from "@/Components/Layout/NotificationWrapper";

export const BackendNotifications = () => {
    const { notifications } = usePage<any>().props;

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
