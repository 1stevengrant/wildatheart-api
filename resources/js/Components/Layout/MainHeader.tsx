import { Link, usePage } from "@inertiajs/react";

import { AuthNav } from "@/Components/Layout/AuthNav";
import { MyProfileNavMobile } from "@/Components/Layout/MyProfileNavMobile";
import { ToggleSupportRequestButton } from "@/Components/Layout/ToggleSupportRequestButton";
import Logo from "@/Components/Logo";

export const MainHeader = () => {
    const { auth } = usePage<any>().props;
    const url = route().current();
    const shouldDisplayMenu = () => {
        return url
            ? ["profile", "events", "connections", "resources"].some((str) =>
                  url.includes(str)
              )
            : false;
    };

    const textStyles = `hover:border-orange hover:text-orange transition ease-in-out duration-150`;

    return (
        <header
            className={`px-2 py-4 md:p-4 bg-black text-white flex items-center justify-between space-x-2 md:space-x-4 w-full`}
        >
            <div className={`flex items-center xl:w-1/2`}>
                <Logo
                    className={
                        "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mr-2"
                    }
                />
                <Link
                    className={`ml-4 border-b-2 pb-2 ${url?.includes("home") ? `border-white` : `border-transparent`}`}
                    href={"/"}
                >
                    Events
                </Link>
            </div>

            {auth.user && shouldDisplayMenu() ? <MyProfileNavMobile /> : <></>}
            <div className={`hidden lg:w-1/2 lg:flex lg:justify-end`}>
                <div className={`flex items-center space-x-4`}>
                    <ToggleSupportRequestButton />
                    <Link href={`/host`} className={`${textStyles}`}>
                        Host an Event
                    </Link>
                    <button
                        className={`rounded-full  border border-white px-4 py-2 ${textStyles}`}
                    >
                        Learn More
                    </button>

                    <AuthNav />
                </div>
            </div>
        </header>
    );
};
