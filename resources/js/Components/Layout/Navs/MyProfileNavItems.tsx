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
        title: "My Host Profile",
        url: "my.profile",
    },
    {
        icon: <FireIcon className={defaultIconSize} />,
        title: "My Events",
        url: "my.events.active",
    },
    {
        icon: <LinkIcon className={defaultIconSize} />,
        title: "My Contacts",
        url: "my.contacts",
    },
    {
        icon: <BookOpenIcon className={defaultIconSize} />,
        title: "Resources",
        url: "resources",
    },
];
