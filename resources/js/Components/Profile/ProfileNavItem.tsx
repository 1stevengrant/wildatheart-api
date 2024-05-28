import React from "react";

import { Link } from "@inertiajs/react";
import classNames from "classnames";

import { profileNavItemClasses } from "@/helpers/profileNavItemClasses";

interface ProfileNavItemProps {
    icon: React.ReactNode;
    link: string;
    text: string;
}

export const ProfileNavItem = ({ icon, link, text }: ProfileNavItemProps) => {
    const isActive = route().current(link + "*");

    const textClasses = classNames({
        "text-orange bg-grey-darkest": isActive,
        "text-white group-hover:text-white": !isActive,
    });

    return (
        <Link
            href={route(link)}
            className={`${profileNavItemClasses} ${isActive} ${textClasses}`}
        >
            {icon}
            <div>{text}</div>
        </Link>
    );
};
