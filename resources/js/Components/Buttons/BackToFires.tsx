import React from "react";
import {ArrowLeftIcon} from "@heroicons/react/20/solid";
import {Link} from "@inertiajs/react";

type BackToFiresProps = {
    positionClasses?: string;
    bgColorClasses?: string;
};

const BackToFires = ({
                         positionClasses = "absolute left-4 top-[20px]",
                         bgColorClasses = "bg-black"
                     }: BackToFiresProps) => {
    const combinedClasses = `${positionClasses} ${bgColorClasses} z-10 flex items-center space-x-2 rounded-full px-3 py-1 text-white`;

    return (
        <Link href={route('home')} className={combinedClasses}>
            <ArrowLeftIcon className={'w-6 h-6'}/>
            <span className="text-sm">Back to Fires</span>
        </Link>
    )
};

export default BackToFires;
