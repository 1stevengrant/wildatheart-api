import React from 'react'
import {FireIcon, HandRaisedIcon, UserCircleIcon} from "@heroicons/react/24/outline";
import FooterNavLink from "@/Components/FooterNavLink";
import {usePage} from '@inertiajs/react'
import {Errors, Ziggy} from "@/types";
import {userPhoto} from "@/helpers/user";
import UserAvatar from "@/Components/UserAvatar";

const iconSize = 'w-6 h-6';
const linkClass = ' flex flex-col items-center space-y-1 text-sm ';

const Header = () => {
    const {auth} = usePage<any>().props;

    return (
        <footer className="p-4 bg-black text-white md:hidden">

            <nav className={'flex items-center space-x-6 justify-center'}>
                <FooterNavLink
                    href={route('home')}
                    className={linkClass}
                    active={route().current('home')}>
                    <FireIcon className={iconSize}/>
                    <span>Fires</span>
                </FooterNavLink>
                <a href={route('host')}
                   className={linkClass}>
                    <HandRaisedIcon className={iconSize}/>
                    <span>Host</span>
                </a>
                <>
                    {auth.user ? (
                        <a href={route('my.profile')} className={linkClass}>
                            {auth.user.image ? (
                                <UserAvatar image={auth.user.image}
                                            width={64}/>
                            ) : (
                                <UserCircleIcon className={iconSize}/>
                            )}
                            <span>My Profile</span>
                        </a>
                    ) : <a href={route('login')} className={linkClass}>
                        <UserCircleIcon className={iconSize}/>
                        <span>Login</span>
                    </a>
                    }
                </>
            </nav>
        </footer>
    )
}

export default Header
