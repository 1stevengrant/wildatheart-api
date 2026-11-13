import React from "react";

import { Link } from "@inertiajs/react";
import { clsx } from "clsx";

import { profileNavItemClasses } from "@/helpers/profileNavItemClasses";

export const ProfileNavItem = ({
    icon,
    link,
    text,
}: {
    icon: React.ReactNode;
    link: string;
    text: string;
}) => {
    const isActive = route().current(link + "*");

    const textClasses = clsx({
        "text-p200 bg-grey-darkest": isActive,
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
