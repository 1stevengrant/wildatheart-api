import { Link, usePage } from "@inertiajs/react";

import { ToggleSupportRequestButton } from "@/Components/Buttons/ToggleSupportRequestButton";
import { AuthNav } from "@/Components/Layout/Navs/AuthNav";
import { MyProfileNavMobile } from "@/Components/Layout/Navs/MyProfileNavMobile";
import { Logo } from "@/Components/Ui/Logo";
import { wahWebsiteUrl } from "@/helpers/wahWebsiteUrl";
import { blackButtonClasses } from "@/helpers/blackButtonClasses";

export const MainHeader = () => {
    const { auth } = usePage<any>().props;
    const url = route().current();
    const shouldDisplayMenu = () => {
        return url
            ? ["profile", "events", "contacts", "resources"].some((str) =>
                  url.includes(str)
              )
            : false;
    };

    const textStyles = `hover:border-p200 hover:text-p200 transition ease-in-out duration-150`;

    return (
        <header
            className={`px-2 py-4 md:p-4 bg-black text-white flex items-center justify-between space-x-2 md:space-x-4 w-full`}
        >
            <div className={`flex items-center xl:w-1/2`}>
                <a href={wahWebsiteUrl} className={`block`}>
                    <Logo
                        className={
                            "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mr-2 opacity-80"
                        }
                    />
                </a>
            </div>

            {auth.user && shouldDisplayMenu() ? <MyProfileNavMobile /> : <></>}
            <div className={`hidden lg:w-1/2 lg:flex lg:justify-end`}>
                <div className={`flex items-center space-x-4`}>
                    <ToggleSupportRequestButton />
                    <Link
                        href={route("host.index")}
                        className={`${textStyles}`}
                    >
                        Host an Event
                    </Link>
                    <a
                        href={`https://wildatheart.org/events`}
                        className={`${blackButtonClasses}`}
                    >
                        Learn More
                    </a>

                    <AuthNav />
                </div>
            </div>
        </header>
    );
};
