import React from "react";

import { useRecoilState } from "recoil";

import { Notification } from "@/Components/Layout/Notification";
import { NotificationWrapper } from "@/Components/Layout/NotificationWrapper";
import { reactNotificationState } from "@/store";

export const ReactNotifications = () => {
    const [reactNotifications] = useRecoilState(reactNotificationState);
    return (
        <>
            {reactNotifications.length > 0 && (
                <NotificationWrapper>
                    {reactNotifications &&
                        reactNotifications.map((notification, index) => (
                            <Notification
                                key={index}
                                message={notification.message}
                                type={notification.type}
                            />
                        ))}
                </NotificationWrapper>
            )}
        </>
    );
};
