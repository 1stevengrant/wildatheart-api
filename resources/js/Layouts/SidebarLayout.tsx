import React, { useState } from "react";

import { Bars3Icon } from "@heroicons/react/24/outline";
import { usePage } from "@inertiajs/react";

import { EventCreateProgressMobile } from "@/Components/Events/EventCreateProgressMobile";
import { SidebarLayoutDesktopSidebar } from "@/Components/Layout/Elements/SidebarLayoutDesktopSidebar";
import { SidebarLayoutMobileSidebar } from "@/Components/Layout/Elements/SidebarLayoutMobileSidebar";

export const SidebarLayout = ({
    children,
    footer,
}: {
    children: React.ReactNode;
    footer: React.ReactNode;
}) => {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { auth } = usePage().props;

    return (
        <>
            <div>
                <SidebarLayoutMobileSidebar
                    auth={auth}
                    setSidebarOpen={setSidebarOpen}
                    sidebarOpen={sidebarOpen}
                />

                <SidebarLayoutDesktopSidebar auth={auth} />

                <div className="sticky top-0 z-40 flex items-center gap-x-6 bg-black px-4 py-4 shadow-sm sm:px-6 lg:hidden">
                    <button
                        type="button"
                        className="text-white -m-2.5 p-2.5 lg:hidden"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <span className="sr-only">Open sidebar</span>
                        <Bars3Icon className="h-6 w-6" aria-hidden="true" />
                    </button>
                    <div className="flex-1 text-sm font-semibold leading-6 text-white">
                        <EventCreateProgressMobile />
                    </div>
                </div>

                <main className="py-10 lg:pl-72">
                    <div className="px-4 sm:px-6 lg:px-8">{children}</div>
                </main>

                {footer}
            </div>
        </>
    );
};
