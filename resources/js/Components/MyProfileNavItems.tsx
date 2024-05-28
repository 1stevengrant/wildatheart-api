import React from "react";

import {
    UserCircleIcon,
    FireIcon,
    LinkIcon,
    BookOpenIcon,
} from "@heroicons/react/24/outline";

import { defaultIconSize } from "@/helpers/defaultIconSize";

export const myProfileNavItems = [
    {
        icon: <UserCircleIcon className={defaultIconSize} />,
        title: "My Fire Host Profile",
        url: "my.profile",
    },
    {
        icon: <FireIcon className={defaultIconSize} />,
        title: "My Events",
        url: "my.events",
    },
    {
        icon: <LinkIcon className={defaultIconSize} />,
        title: "My Connections",
        url: "my.connections",
    },
    {
        icon: <BookOpenIcon className={defaultIconSize} />,
        title: "Resources",
        url: "resources",
    },
];
