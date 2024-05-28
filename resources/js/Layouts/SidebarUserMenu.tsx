import { Menu, MenuButton, MenuItems } from "@headlessui/react";

import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";
import { SidebarUserMenuItems } from "@/Layouts/SidebarUserMenuItems";

export const SidebarUserMenu = ({ auth }: { auth: any }) => {
    return (
        <Menu>
            <MenuButton
                className={`w-full flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-white`}
            >
                <AuthUserAvatar auth={auth} />
                <div>{auth.user.full_name}</div>
            </MenuButton>
            <MenuItems anchor="top start" className={`space-y-2`}>
                <SidebarUserMenuItems />
            </MenuItems>
        </Menu>
    );
};
