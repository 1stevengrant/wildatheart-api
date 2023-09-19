import React, {useState} from 'react'
import {FireIcon, HandRaisedIcon, QuestionMarkCircleIcon, UserCircleIcon} from "@heroicons/react/24/outline";
import FooterNavLink from "@/Components/FooterNavLink";
import {usePage} from '@inertiajs/react';
import UserAvatar from "@/Components/UserAvatar";
import SupportModal from "@/Components/SupportModal";

import {defaultIconSize} from "@/helpers/defaultIconSize";
import {linkClass} from "@/helpers/linkClass";

const Header = () => {
    const {auth} = usePage<any>().props;
    const [open, setOpen] = useState(false)
    return (
        <footer className="p-4 bg-black text-white lg:hidden">

            <nav className={'flex items-center space-x-6 justify-center'}>
                <FooterNavLink
                    href={route('home')}
                    className={linkClass}
                    active={route().current('home')}>
                    <FireIcon className={defaultIconSize}/>
                    <span>Fires</span>
                </FooterNavLink>

                <a href={route('host')}
                   className={linkClass}>
                    <HandRaisedIcon className={defaultIconSize}/>
                    <span>Host</span>
                </a>
                <>
                    {auth.user ? (
                        <a href={route('my.profile')} className={linkClass}>
                            {auth.user.image ? (
                                <UserAvatar image={auth.user.image}
                                            width={64}/>
                            ) : (
                                <UserCircleIcon className={defaultIconSize}/>
                            )}
                            <span>My Profile</span>
                        </a>
                    ) : <a href={route('login')} className={linkClass}>
                        <UserCircleIcon className={defaultIconSize}/>
                        <span>Login</span>
                    </a>
                    }
                </>
                <button className={linkClass}
                        onClick={() => setOpen(true)}>
                    <QuestionMarkCircleIcon className={defaultIconSize}/>
                    <span>Feedback</span>
                </button>
                <SupportModal
                    open={open}
                    setOpen={setOpen}/>
            </nav>
        </footer>
    )
}

export default Header
