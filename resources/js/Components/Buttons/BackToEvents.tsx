import React from "react";
import {ArrowLeftIcon} from "@heroicons/react/20/solid";
import {Link} from "@inertiajs/react";

type BackToEventsProps = {
    positionClasses?: string;
    bgColorClasses?: string;
};

export const BackToEvents = ({
                         positionClasses = "absolute left-4 top-[20px]",
                         bgColorClasses = "bg-black"
                     }: BackToEventsProps) => {
    const combinedClasses = `${positionClasses} ${bgColorClasses} z-10 flex items-center space-x-2 rounded-full px-3 py-1 text-white hover:text-orange transition-colors duration-200 ease-in-out`;

    return (
        <Link href={route('home')} className={combinedClasses}>
            <ArrowLeftIcon className={'w-6 h-6'}/>
            <span className="text-sm">Back to Events</span>
        </Link>
    )
};

export default BackToEvents;
