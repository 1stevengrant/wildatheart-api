import React from "react";
import {Link} from "@inertiajs/react";
import {FireIcon, HandRaisedIcon} from "@heroicons/react/24/outline";
import {linkClass} from "@/helpers/linkClass";

const Nav = () => {
    return (
        <nav className="hidden lg:flex items-center space-x-4 pr-12">
            <Link href={route('home')}
                  className={`${linkClass} ${route().current('home') ? 'text-orange' : ''}`}>
                <FireIcon className={'w-6'}/>
                <span>Find a Fire</span>
            </Link>
            <a href={route('host')}
               className={linkClass}>
                <HandRaisedIcon className={'w-6'}/>
                <span>Host a Fire</span>
            </a>
        </nav>
    )
};
export default Nav;
