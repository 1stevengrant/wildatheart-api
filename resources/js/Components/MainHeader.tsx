import MapboxSearch from "@/Components/Maps/MapboxSearch";
import Logo from "@/Components/Logo";
import {QuestionMarkCircleIcon} from "@heroicons/react/24/outline";
import SupportModal from "@/Components/SupportModal";
import {useState} from "react";

import {linkClass} from "@/helpers/linkClass";
import Nav from "@/Components/Nav";
import AuthNav from "@/Components/AuthNav";

const MainHeader = () => {
    const [open, setOpen] = useState(false)
    return (
        <header className="p-2 sm:p-4 bg-black text-white flex items-center space-x-2 md:space-x-4 w-full">
            <div className={'md:w-1/3 md:flex md:items-center md:space-x-4'}>
                <Logo className={'w-[75px] flex-shrink-0 md:w-[100px] lg:w-[150px]'}/>
                <Nav />
            </div>

            <div className={'w-full lg:w-1/3'}>
                <MapboxSearch/>
            </div>

            <div className={'hidden lg:w-1/3 lg:flex md:justify-end'}>
                <div className="flex items-center space-x-4">
                    <button onClick={() => setOpen(true)}
                            className={linkClass}>
                        <QuestionMarkCircleIcon className={'w-6'}/>
                        <span>Feedback</span>
                    </button>
                    <SupportModal
                        open={open}
                        setOpen={setOpen}
                    />
                    <AuthNav />
                </div>
            </div>

        </header>
    )
}

export default MainHeader
