import React from "react";
import Notification from "@/Components/Notification";
import {usePage} from "@inertiajs/react";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";

interface TwoColumnWrapperProps {
    className?: string;
    children: React.ReactNode;
}

const TwoColumnWrapper = ({className, children}: TwoColumnWrapperProps) => {
    const {notifications} = usePage<any>().props;
    const [reactNotifications, setReactNotifications] = useRecoilState(reactNotificationAtom);
    return (
        <>
            <div className={className + ' md:flex space-x-8 min-h-[100dvh] md:min-h-auto md:h-screen-[100dvh] px-4' +
                ' py-4'}>
                {children}
            </div>
            {notifications && (
                <Notification
                    message={notifications.message}
                />
            )}
            {reactNotifications && reactNotifications.map((notification, index) => (
                <Notification
                    key={index}
                    message={notification.message}
                    type={notification.type}
                />
            ))}
        </>
    );
}

export default TwoColumnWrapper;
