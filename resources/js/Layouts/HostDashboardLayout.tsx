import React, { useState } from "react";

import { Dialog, Transition } from "@headlessui/react";
import {
    Bars3Icon,
    CalendarDaysIcon,
    LifebuoyIcon,
    PuzzlePieceIcon,
    UserIcon,
    UsersIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";
import { Head, Link, usePage } from "@inertiajs/react";
import classNames from "classnames";

import { AuthUserMenu } from "@/Components/Layout/Elements/AuthUserMenu";
import { BackendNotifications } from "@/Components/Layout/Notifications/BackendNotifications";
import { ReactNotifications } from "@/Components/Layout/Notifications/ReactNotifications";
import { SupportModal } from "@/Components/Modals/SupportModal";
import { Logo } from "@/Components/Ui/Logo";
import { wahWebsiteUrl } from "@/helpers/wahWebsiteUrl";

export const HostDashboardLayout = ({
    children,
    pageTitle,
}: {
    children: React.ReactNode;
    pageTitle: string;
}) => {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [openSupportModal, setOpenSupportModal] = useState(false);
    const { auth } = usePage<any>().props;

    const navigation = [
        {
            name: "My Host Profile",
            href: route("my.profile"),
            icon: UserIcon,
            current: route().current("my.profile"),
        },
        {
            name: "Event Resources",
            href: route("resources"),
            icon: PuzzlePieceIcon,
            current: route().current("resources"),
        },
        {
            name: "My Events",
            href: route("my.events.active"),
            icon: CalendarDaysIcon,
            current: route().current("my.events.*"),
        },
        {
            name: "My Contacts",
            href: route("my.contacts"),
            icon: UsersIcon,
            current: route().current("my.contacts"),
        },
        {
            name: "Support",
            href: null,
            action: () => setOpenSupportModal(true),
            icon: LifebuoyIcon,
            current: false,
        },
    ];

    return (
        <>
            <Head title={pageTitle} />
            <div>
                <SupportModal
                    open={openSupportModal}
                    setOpen={setOpenSupportModal}
                />
                <Transition show={sidebarOpen}>
                    <Dialog
                        className="relative z-50 lg:hidden"
                        onClose={setSidebarOpen}
                    >
                        <Transition.Child
                            enter="transition-opacity ease-linear duration-300"
                            enterFrom="opacity-0"
                            enterTo="opacity-100"
                            leave="transition-opacity ease-linear duration-300"
                            leaveFrom="opacity-100"
                            leaveTo="opacity-0"
                        >
                            <div className="fixed inset-0 bg-gray-900/80" />
                        </Transition.Child>

                        <div className="fixed inset-0 flex">
                            <Transition.Child
                                enter="transition ease-in-out duration-300 transform"
                                enterFrom="-translate-x-full"
                                enterTo="translate-x-0"
                                leave="transition ease-in-out duration-300 transform"
                                leaveFrom="translate-x-0"
                                leaveTo="-translate-x-full"
                            >
                                <Dialog.Panel className="relative mr-16 flex w-full max-w-xs flex-1">
                                    <Transition.Child
                                        enter="ease-in-out duration-300"
                                        enterFrom="opacity-0"
                                        enterTo="opacity-100"
                                        leave="ease-in-out duration-300"
                                        leaveFrom="opacity-100"
                                        leaveTo="opacity-0"
                                    >
                                        <div className="absolute top-0 left-full flex w-16 justify-center pt-5">
                                            <button
                                                type="button"
                                                className="-m-2.5 p-2.5"
                                                onClick={() =>
                                                    setSidebarOpen(false)
                                                }
                                            >
                                                <span className="sr-only">
                                                    Close sidebar
                                                </span>
                                                <XMarkIcon
                                                    className="h-6 w-6 text-white"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                        </div>
                                    </Transition.Child>
                                    {/* Sidebar component, swap this element with another sidebar if you like */}
                                    <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4 h-screen">
                                        <a href={wahWebsiteUrl}>
                                            <Logo
                                                className={
                                                    "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mt-6"
                                                }
                                            />
                                        </a>
                                        <nav className="flex flex-1 flex-col">
                                            <ul
                                                role="list"
                                                className="flex flex-1 flex-col gap-y-7"
                                            >
                                                <li>
                                                    <ul
                                                        role="list"
                                                        className="-mx-2 space-y-1"
                                                    >
                                                        {navigation.map(
                                                            (item) => (
                                                                <li
                                                                    key={
                                                                        item.name
                                                                    }
                                                                >
                                                                    {item.action && (
                                                                        <button
                                                                            onClick={
                                                                                item.action
                                                                            }
                                                                            className={classNames(
                                                                                "group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold",
                                                                                "hover:text-white hover:bg-white/100"
                                                                            )}
                                                                        >
                                                                            <item.icon
                                                                                className={classNames(
                                                                                    "text-white group-hover:text-white",
                                                                                    "h-6 w-6 shrink-0"
                                                                                )}
                                                                                aria-hidden="true"
                                                                            />
                                                                            {
                                                                                item.name
                                                                            }
                                                                        </button>
                                                                    )}
                                                                    {item.href && (
                                                                        <Link
                                                                            href={
                                                                                item.href
                                                                            }
                                                                            className={classNames(
                                                                                item.current
                                                                                    ? "bg-white/10 text-white"
                                                                                    : "hover:text-white hover:bg-white/100",
                                                                                "group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold"
                                                                            )}
                                                                        >
                                                                            <item.icon
                                                                                className={classNames(
                                                                                    item.current
                                                                                        ? "text-white"
                                                                                        : "text-white group-hover:text-white",
                                                                                    "h-6 w-6 shrink-0"
                                                                                )}
                                                                                aria-hidden="true"
                                                                            />
                                                                            {
                                                                                item.name
                                                                            }
                                                                        </Link>
                                                                    )}
                                                                </li>
                                                            )
                                                        )}
                                                    </ul>
                                                </li>

                                                <AuthUserMenu auth={auth} />
                                            </ul>
                                        </nav>
                                    </div>
                                </Dialog.Panel>
                            </Transition.Child>
                        </div>
                    </Dialog>
                </Transition>

                {/* Static sidebar for desktop */}
                <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
                    {/* Sidebar component, swap this element with another sidebar if you like */}
                    <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6 pb-4 border-r border-grey-border">
                        <a href={wahWebsiteUrl}>
                            <Logo
                                className={
                                    "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mt-6"
                                }
                            />
                        </a>
                        <nav className="flex flex-1 flex-col">
                            <ul
                                role="list"
                                className="flex flex-1 flex-col gap-y-7"
                            >
                                <li>
                                    <ul role="list" className="-mx-2 space-y-1">
                                        {navigation.map((item) => (
                                            <li key={item.name}>
                                                {item.action && (
                                                    <button
                                                        onClick={item.action}
                                                        className={classNames(
                                                            item.current
                                                                ? " bg-white/10 text-white"
                                                                : "hover:text-white hover:bg-white/10",
                                                            "group flex gap-x-3 rounded-md p-4 leading-6 font-semibold w-full"
                                                        )}
                                                    >
                                                        <item.icon
                                                            className={classNames(
                                                                item.current
                                                                    ? "text-white"
                                                                    : "group-hover:text-white",
                                                                "h-6 w-6 shrink-0"
                                                            )}
                                                            aria-hidden="true"
                                                        />
                                                        {item.name}
                                                    </button>
                                                )}
                                                {item.href && (
                                                    <Link
                                                        href={item.href}
                                                        className={classNames(
                                                            item.current
                                                                ? "bg-white/10 text-white"
                                                                : "hover:text-white hover:bg-white/10",
                                                            "group flex gap-x-3 rounded-md p-4 leading-6 font-semibold"
                                                        )}
                                                    >
                                                        <item.icon
                                                            className={classNames(
                                                                item.current
                                                                    ? "text-white"
                                                                    : "group-hover:text-white",
                                                                "h-6 w-6 shrink-0"
                                                            )}
                                                            aria-hidden="true"
                                                        />
                                                        {item.name}
                                                    </Link>
                                                )}
                                            </li>
                                        ))}
                                    </ul>
                                </li>
                                <AuthUserMenu auth={auth} />
                            </ul>
                        </nav>
                    </div>
                </div>

                <div className="lg:pl-72">
                    <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 bg-black px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 md:hidden">
                        <button
                            type="button"
                            className="text-white -m-2.5 p-2.5 lg:hidden"
                            onClick={() => setSidebarOpen(true)}
                        >
                            <span className="sr-only">Open sidebar</span>
                            <Bars3Icon className="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>

                    <main>
                        <div className="px-4 sm:px-6 lg:px-8 md:pt-12">
                            {children}
                        </div>
                    </main>
                </div>
            </div>
            <BackendNotifications />
            <ReactNotifications />
        </>
    );
};
