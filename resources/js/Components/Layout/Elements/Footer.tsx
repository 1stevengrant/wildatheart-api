import React, { useState } from "react";

import {
    FireIcon,
    HandRaisedIcon,
    HomeIcon,
    QuestionMarkCircleIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";
import { Link, usePage } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import FooterNavLink from "@/Components/Layout/Elements/FooterNavLink";
import { SupportModal } from "@/Components/Modals/SupportModal";
import { UserAvatar } from "@/Components/UserAvatar";
import { defaultIconSize } from "@/helpers/defaultIconSize";
import { linkClass } from "@/helpers/linkClass";
import { showFilterPanelState } from "@/store";
import { searchResultsState } from "@/store/searchResultsAtom";

export const Footer = ({ showFooter = true }: { showFooter?: boolean }) => {
    const { auth } = usePage<any>().props;
    const [open, setOpen] = useState(false);
    const [searchResults] = useRecoilState(searchResultsState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    return (
        <>
            {showFooter && !searchResults && !showFilterPanel && (
                <footer className="bg-black p-4 text-white lg:hidden">
                    <nav
                        className={"flex items-center space-x-6 justify-center"}
                    >
                        <FooterNavLink
                            href={route("home")}
                            className={` ${linkClass} `}
                            active={route().current("home")}
                        >
                            <HomeIcon className={`w-6`} />
                            <span>Home</span>
                        </FooterNavLink>

                        {auth.user ? (
                            <Link
                                href={route("host")}
                                className={` ${linkClass} `}
                            >
                                <HandRaisedIcon className={"w-6"} />
                                <span>Host</span>
                            </Link>
                        ) : (
                            <a href={route("login")} className={linkClass}>
                                <HandRaisedIcon className={"w-6"} />
                                <span>Host</span>
                            </a>
                        )}
                        <>
                            {auth.user ? (
                                <Link
                                    href={route("my.profile")}
                                    className={linkClass}
                                >
                                    {auth.user.image ? (
                                        <UserAvatar image={auth.user.image} />
                                    ) : (
                                        <UserCircleIcon
                                            className={defaultIconSize}
                                        />
                                    )}
                                    <span>My Profile</span>
                                </Link>
                            ) : (
                                <a href={route("login")} className={linkClass}>
                                    <UserCircleIcon
                                        className={defaultIconSize}
                                    />
                                    <span>Login</span>
                                </a>
                            )}
                        </>
                        <button
                            className={linkClass}
                            onClick={() => setOpen(true)}
                        >
                            <QuestionMarkCircleIcon
                                className={defaultIconSize}
                            />
                            <span>Feedback</span>
                        </button>
                        <SupportModal open={open} setOpen={setOpen} />
                    </nav>
                </footer>
            )}
        </>
    );
};
