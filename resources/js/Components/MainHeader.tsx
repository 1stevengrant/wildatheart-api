import { useState } from "react";

import { QuestionMarkCircleIcon } from "@heroicons/react/24/outline";
import { usePage } from "@inertiajs/react";

import AuthNav from "@/Components/AuthNav";
import Logo from "@/Components/Logo";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import MyProfileNavMobile from "@/Components/MyProfileNavMobile";
import Nav from "@/Components/Nav";
import SupportModal from "@/Components/SupportModal";
import { linkClass } from "@/helpers/linkClass";

const MainHeader = () => {
    const [open, setOpen] = useState(false);
    const { auth } = usePage<any>().props;
    const url = route().current();

    const shouldDisplayMenu = () => {
        return url
            ? ["profile", "events", "connections", "resources"].some((str) =>
                  url.includes(str)
              )
            : false;
    };

    const shouldDisplaySearch = () => {
        return url ? url === "home" : false;
    };

    return (
        <header
            className={`px-2 py-4 md:p-4 bg-black text-white flex items-center space-x-2 md:space-x-4 w-full`}
        >
            <div className={`flex items-center xl:w-1/3`}>
                <a href={"https://wildatheart.org"} className={` mr-2`}>
                    <Logo
                        className={
                            "w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px]"
                        }
                    />
                </a>
                <Nav />
            </div>

            <div className={`w-full lg:w-1/3`}>
                {shouldDisplaySearch() ? <MapboxSearch /> : <div></div>}
            </div>

            {auth.user && shouldDisplayMenu() ? <MyProfileNavMobile /> : <></>}

            <div className={`hidden lg:w-1/3 lg:flex lg:justify-end`}>
                <div className={`flex items-center space-x-4`}>
                    <button onClick={() => setOpen(true)} className={linkClass}>
                        <QuestionMarkCircleIcon className={"w-6"} />
                        <span>Feedback</span>
                    </button>
                    <SupportModal open={open} setOpen={setOpen} />
                    <AuthNav />
                </div>
            </div>
        </header>
    );
};

export default MainHeader;
