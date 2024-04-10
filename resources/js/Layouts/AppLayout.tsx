import React, { PropsWithChildren } from "react";

import { Head } from "@inertiajs/react";

import { BackendNotifications } from "@/Components/Layout/BackendNotifications";
import { Footer } from "@/Components/Layout/Footer";
import { MainHeader } from "@/Components/Layout/MainHeader";
import { ReactNotifications } from "@/Components/Layout/ReactNotifications";

interface IAppLayoutProps {
    title: string;
    showFooter?: boolean;
    className?: string;
    childClassName?: string;
}

export const AppLayout = ({
    title,
    children,
    showFooter,
    className = "",
    childClassName = "",
}: PropsWithChildren<IAppLayoutProps>) => {
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
