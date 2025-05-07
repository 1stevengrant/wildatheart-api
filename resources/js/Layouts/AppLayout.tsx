import { PropsWithChildren } from "react";

import { Head } from "@inertiajs/react";

import { Footer } from "@/Components/Layout/Elements/Footer";
import { MainHeader } from "@/Components/Layout/Elements/MainHeader";
import { BackendNotifications } from "@/Components/Layout/Notifications/BackendNotifications";
import { ReactNotifications } from "@/Components/Layout/Notifications/ReactNotifications";

export const AppLayout = ({
    title,
    children,
    showFooter,
    className = "",
    childClassName = "",
}: PropsWithChildren<{
    title: string;
    showFooter?: boolean;
    className?: string;
    childClassName?: string;
}>) => {
    return (
        <div className={`flex flex-col h-[100dvh] ${className}`}>
            <Head title={title} />
            <MainHeader />
            <main className={`flex-1 overflow-y-auto ${childClassName}`}>
                {children}
            </main>
            <Footer showFooter={showFooter} />
            <BackendNotifications />
            <ReactNotifications />
        </div>
    );
};
