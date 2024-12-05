import React from "react";

import { ArrowLeftIcon } from "@heroicons/react/20/solid";
import { Link } from "@inertiajs/react";

type BackToEventsProps = {
    positionClasses?: string;
    bgColorClasses?: string;
};

export const BackToEvents = ({
    positionClasses = "absolute left-4 top-[20px]",
    bgColorClasses = "bg-white",
}: BackToEventsProps) => {
    const combinedClasses = `${positionClasses} ${bgColorClasses} z-10 flex items-center space-x-2 rounded-full px-4 py-2 text-black font-medium hover:text-p200 transition duration-200 ease-in-out shadow-xl hover:shadow-none`;

    return (
        <Link href={route("home")} className={combinedClasses}>
            <ArrowLeftIcon className={"w-6 h-6"} />
            <span className="text-sm">Back to Directory</span>
        </Link>
    );
};
