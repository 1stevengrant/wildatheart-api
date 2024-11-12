import { Menu } from "@headlessui/react";
import {
    ArrowTopRightOnSquareIcon,
    HomeIcon,
    MapIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";

import { wahWebsiteUrl } from "@/helpers/wahWebsiteUrl";

const linkClasses = `flex items-center space-x-3 hover:text-orange`;

const navItems = [
    {
        icon: <MapIcon className={`w-6 h-6`} />,
        title: `Back to Map`,
        href: route("home"),
    },
    {
        icon: <HomeIcon className={`w-6 h-6`} />,
        title: `Wild at Heart`,
        href: wahWebsiteUrl,
    },
    {
        icon: <UserCircleIcon className={`w-6 h-6`} />,
        title: `My Profile`,
        href: route("my.profile"),
    },
    {
        icon: <ArrowTopRightOnSquareIcon className={`w-6 h-6`} />,
        title: `Logout`,
        href: route("logout"),
    },
];
export const SidebarUserMenuItems = () => {
    return (
        <>
            {navItems.map((item, index) => (
                <Menu.Item key={index}>
                    <Link className={linkClasses} href={item.href}>
                        {item.icon}
                        <div>{item.title}</div>
                    </Link>
                </Menu.Item>
            ))}
        </>
    );
};
