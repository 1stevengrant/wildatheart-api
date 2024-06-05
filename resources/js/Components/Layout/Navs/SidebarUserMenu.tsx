import { Menu } from "@headlessui/react";

import { SidebarUserMenuItems } from "@/Components/Layout/Navs/SidebarUserMenuItems";
import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";

export const SidebarUserMenu = ({ auth }: { auth: any }) => {
    return (
        <Menu>
            <Menu.Button
                className={`w-full flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-white`}
            >
                <AuthUserAvatar auth={auth} />
                <div>{auth.user.full_name}</div>
            </Menu.Button>
            <Menu.Items className={`space-y-2`}>
                <SidebarUserMenuItems />
            </Menu.Items>
        </Menu>
    );
};
