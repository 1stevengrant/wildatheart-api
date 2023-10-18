import React from 'react';
import {
    UserCircleIcon, FireIcon, LinkIcon, BookOpenIcon
} from "@heroicons/react/24/outline";
import {defaultIconSize} from "@/helpers/defaultIconSize";

const myProfileNavItems = [
    {
        icon: <UserCircleIcon className={defaultIconSize}/>,
        title: 'My Fire Host Profile',
        url: 'my.profile',
    },
    {
        icon: <FireIcon className={defaultIconSize}/>,
        title: 'My Fires',
        url: 'my.campfires',
    },
    {
        icon: <LinkIcon className={defaultIconSize}/>,
        title: 'My Connections',
        url: 'my.connections',
    },
    {
        icon: <BookOpenIcon className={defaultIconSize}/>,
        title: 'Resources',
        url: 'resources',
    }
];

export default myProfileNavItems;
