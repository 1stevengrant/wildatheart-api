import { MenuItem } from "@headlessui/react";
import { HomeIcon, MapIcon, UserCircleIcon } from "@heroicons/react/24/outline";
import { Link } from "@inertiajs/react";

import { wahWebsiteUrl } from "@/helpers/wahWebsiteUrl";

const linkClasses = `flex items-center space-x-3 hover:text-orange`;
export const SidebarUserMenuItems = () => {
    return (
        <>
            <MenuItem>
                <Link className={linkClasses} href={route("home")}>
                    <MapIcon className={`w-6 h-6`} />
                    <div>Back to Map</div>
                </Link>
            </MenuItem>
            <MenuItem>
                <a className={linkClasses} href={wahWebsiteUrl}>
                    <HomeIcon className={`w-6 h-6`} />
                    <div>Wild at Heart</div>
                </a>
            </MenuItem>
            <MenuItem>
                <Link className={linkClasses} href={route("my.profile")}>
                    <UserCircleIcon className={`w-6 h-6`} />
                    <div>My Profile</div>
                </Link>
            </MenuItem>
        </>
    );
};
