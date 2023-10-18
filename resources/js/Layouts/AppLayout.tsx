import React, {PropsWithChildren} from "react";
import MainHeader from "@/Components/MainHeader";
import Footer from "@/Components/Footer";
import Notification from "@/Components/Notification";
import {Head, usePage} from "@inertiajs/react";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";
import NotificationWrapper from "@/Components/NotificationWrapper";

interface Props {
    title: string;
    showFooter?: boolean;
}

const AppLayout = ({
                       title,
                       children,
                       showFooter = true,
                   }: PropsWithChildren<Props>) => {

    const {notifications} = usePage<any>().props;
    const [reactNotifications] = useRecoilState(reactNotificationAtom);

    return (
        <div className="flex flex-col h-[100dvh]">
            <Head title={title}/>
            <MainHeader/>
            <main className={'flex-1 overflow-y-auto'}>{children}</main>
            {showFooter && <Footer/>}
            {notifications && (
                <NotificationWrapper>
                    <Notification
                        message={notifications.message}
                    />
                </NotificationWrapper>
            )}
            {reactNotifications.length > 0 && (
                <NotificationWrapper>
                    {reactNotifications && reactNotifications.map((notification, index) => (
                        <Notification
                            key={index}
                            message={notification.message}
                            type={notification.type}
                        />

                    ))}
                </NotificationWrapper>
            )}
        </div>
    )
}

export default AppLayout
