import React, { useState } from "react";

import { Menu } from "@headlessui/react";
import OutsideClickHandler from "react-outside-click-handler";

import { SidebarUserMenuItems } from "@/Components/Layout/Navs/SidebarUserMenuItems";
import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";

export const AuthUserMenu = ({ auth }: { auth: any }) => {
    const [showUserMenu, setShowUserMenu] = useState(false);
    const toggleMenu = () => {
        setShowUserMenu(!showUserMenu);
    };
    return (
        <li className="-mx-6 mt-auto relative">
            <OutsideClickHandler onOutsideClick={() => setShowUserMenu(false)}>
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
                    <span aria-hidden="true">{auth.user.full_name}</span>
                </button>
            </OutsideClickHandler>
        </li>
    );
};
