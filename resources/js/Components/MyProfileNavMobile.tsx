import React, {useState} from "react";
import {Bars3Icon} from "@heroicons/react/24/solid";
import {XMarkIcon} from "@heroicons/react/24/outline";
import myProfileNavItems from '@/helpers/myProfileNavItems'
import ProfileNavItem from "@/Components/Profile/ProfileNavItem";
import LogoutButtonWithModal from "@/Components/LogoutButtonWithModal";

interface MyProfileNavProps {
    className?: string
}

const MyProfileNavMobile = ({className}: MyProfileNavProps) => {
    const [menuOpen, setMenuOpen] = useState(false)
    return (
        <div className={`${className} z-10 md:hidden`}>
            <button onClick={() => setMenuOpen(!menuOpen)}>
                {menuOpen ? (
                    <XMarkIcon className={`w-8 h-8`}/>
                ) : (
                    <Bars3Icon className={`w-8 h-8`}/>
                )}
            </button>
            {menuOpen && (
                <nav className={`absolute w-full bg-black right-0 left-0 p-4 top-[65px]`}>
                    {myProfileNavItems.map((item, index) => (
                        <ProfileNavItem
                            icon={item.icon}
                            link={item.url}
                            text={item.title}
                            key={index}
                        />
                    ))}
                    <LogoutButtonWithModal/>
                </nav>
            )}
        </div>
    )
}

export default MyProfileNavMobile
