import { Fragment, useState } from "react";

import { Menu, Transition } from "@headlessui/react";
import { usePage } from "@inertiajs/react";
import classNames from "classnames";

import { LogoutModal } from "@/Components/Modals/LogoutModal";
import { AuthUserAvatar } from "@/Components/Ui/AuthUserAvatar";
import { linkClass } from "@/helpers/linkClass";

export const AuthNav = () => {
    const { auth } = usePage().props;
    const [open, setOpen] = useState(false);
    return (
        <>
            {auth.user ? (
                <Menu as="div" className="relative ml-3 z-50">
                    <div>
                        <Menu.Button className="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span className="absolute -inset-1.5" />
                            <span className="sr-only">Open user menu</span>

                            <AuthUserAvatar auth={auth} />
                        </Menu.Button>
                    </div>
                    <Transition
                        as={Fragment}
                        enter="transition ease-out duration-100"
                        enterFrom="transform opacity-0 scale-95"
                        enterTo="transform opacity-100 scale-100"
                        leave="transition ease-in duration-75"
                        leaveFrom="transform opacity-100 scale-100"
                        leaveTo="transform opacity-0 scale-95"
                    >
                        <Menu.Items className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <Menu.Item>
                                {({ active }) => (
                                    <a
                                        href={route("my.profile")}
                                        className={classNames(
                                            active ? "bg-grey-lighter" : "",
                                            "block px-4 py-2" + " text-black"
                                        )}
                                    >
                                        My Profile
                                    </a>
                                )}
                            </Menu.Item>
                            <Menu.Item>
                                {({ active }) => (
                                    <>
                                        <button
                                            onClick={() => setOpen(true)}
                                            className={classNames(
                                                active ? "bg-grey-lighter" : "",
                                                `block px-4 py-2 text-black hover:bg-grey-lighter w-full text-left`
                                            )}
                                        >
                                            Logout
                                        </button>
                                        <LogoutModal
                                            open={open}
                                            setOpen={setOpen}
                                        />
                                    </>
                                )}
                            </Menu.Item>
                        </Menu.Items>
                    </Transition>
                </Menu>
            ) : (
                <a href={route("login")} className={linkClass}>
                    <span>Login / Register</span>
                </a>
            )}
        </>
    );
};
