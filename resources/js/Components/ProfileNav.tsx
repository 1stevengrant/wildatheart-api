import React from "react";
import ProfileNavItem from "@/Components/Profile/ProfileNavItem";
import myProfileNavItems from "@/helpers/myProfileNavItems";
import LogoutButtonWithModal from "@/Components/LogoutButtonWithModal";

const ProfileNav = () => {

    return (
        <nav
            className={`hidden flex-col items-center space-y-6 border-r border-r-grey-darker p-4 md:flex min-w-[300px] md:px-7 md:py-6`}>
            {myProfileNavItems.map((navItem, index) => (
                <ProfileNavItem
                    icon={navItem.icon}
                    link={navItem.url}
                    text={navItem.title}
                    key={index}
                />
            ))}
            <LogoutButtonWithModal/>
        </nav>
    )
};

export default ProfileNav;
