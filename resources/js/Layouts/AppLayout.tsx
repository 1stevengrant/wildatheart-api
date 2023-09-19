import React from "react";
import MainHeader from "@/Components/MainHeader";
import Footer from "@/Components/Footer";
import Notification from "@/Components/Notification";
import {usePage} from "@inertiajs/react";

const AppLayout = ({children}: { children?: JSX.Element[] | JSX.Element }) => {
    const {notifications} = usePage<any>().props;
    return (
        <div className="flex flex-col h-[100dvh]">
            <MainHeader/>
            <main className={'flex-1 overflow-y-auto'}>{children}</main>
            <Footer/>
            {notifications && (
                <Notification
                    message={notifications.message}
                />
            )}
        </div>
    )
}

export default AppLayout
