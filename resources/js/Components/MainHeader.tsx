import MapboxSearch from "@/Components/Maps/MapboxSearch";
import Logo from "@/Components/Logo";
import {QuestionMarkCircleIcon} from "@heroicons/react/24/outline";
import SupportModal from "@/Components/SupportModal";
import {useState} from "react";

import {linkClass} from "@/helpers/linkClass";
import Nav from "@/Components/Nav";
import AuthNav from "@/Components/AuthNav";
import MyProfileNavMobile from "@/Components/MyProfileNavMobile";
import {usePage} from "@inertiajs/react";

const MainHeader = () => {
    const [open, setOpen] = useState(false)
    const {auth} = usePage<any>().props
    const shouldDisplayMenu = () => {
        const url = route().current();
        if (url) {
            return url.includes('profile') || url.includes('fires') || url.includes('connections') || url.includes('resources')
        }
        return false
    }
    return (
        <header className={`px-2 py-4 md:p-4 bg-black text-white flex items-center space-x-2 md:space-x-4 w-full`}>
            <div className={`flex items-center xl:w-1/3`}>
                <Logo className={'w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px] mr-2'}/>
                <Nav/>
            </div>

            <div className={`w-full lg:w-1/3`}>
                <MapboxSearch/>
            </div>

            {auth.user && shouldDisplayMenu() ? (
                <MyProfileNavMobile/>
            ) : (<></>)}

            <div className={`hidden lg:w-1/3 lg:flex lg:justify-end`}>
                <div className={`flex items-center space-x-4`}>
                    <button onClick={() => setOpen(true)}
                            className={linkClass}>
                        <QuestionMarkCircleIcon className={'w-6'}/>
                        <span>Feedback</span>
                    </button>
                    <SupportModal
                        open={open}
                        setOpen={setOpen}
                    />
                    <AuthNav/>
                </div>
            </div>

        </header>
    )
}

export default MainHeader
