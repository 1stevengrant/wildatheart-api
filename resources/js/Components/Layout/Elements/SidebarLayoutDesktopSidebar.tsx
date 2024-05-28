import React, { useState } from "react";

import { EventCreateProgress } from "@/Components/Events/EventCreateProgress";
import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";
import { Logo } from "@/Components/Ui/Logo";
import { wahWebsiteUrl } from "@/helpers/wahWebsiteUrl";
import { SidebarUserMenuItems } from "@/Components/Layout/Navs/SidebarUserMenuItems";
import { Menu } from "@headlessui/react";

export const SidebarLayoutDesktopSidebar = ({ auth }: { auth: any }) => {
    const [showUserMenu, setShowUserMenu] = useState(false);
    const toggleMenu = () => {
        setShowUserMenu(!showUserMenu);
    };

    return (
        <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-black px-6">
                <a href={wahWebsiteUrl}>
                    <Logo
                        className={
                            "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mt-6"
                        }
                    />
                </a>
                <EventCreateProgress />
                <nav className="flex flex-1 flex-col">
                    <ul role="list" className="flex flex-1 flex-col gap-y-7">
                        <li className="-mx-6 mt-auto relative">
                            {showUserMenu && (
                                <div className={`absolute left-6 -top-20`}>
                                    <Menu>
                                        <SidebarUserMenuItems />
                                    </Menu>
                                </div>
                            )}

                            <button
                                onClick={() => toggleMenu()}
                                className="flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-white w-full"
                            >
                                <AuthUserAvatar auth={auth} />
                                <span className="sr-only">Your profile</span>
                                <span aria-hidden="true">
                                    {auth.user.full_name}
                                </span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    );
};
