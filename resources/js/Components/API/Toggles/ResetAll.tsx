import React from "react";

import { AllIcon } from "@/Components/Icons/AllIcon";
import { useSearchEvents } from "@/Hooks/useSearchEvents";

export const ResetAll = () => {
    const { resetAll } = useSearchEvents();

    return (
        <button
            className={`text-xxs font-medium flex flex-col justify-center items-center md:px-4 md:py-2 rounded-lg md:border-2 uppercase hover:border-p200 transition duration-300 ease-in-out cursor-pointer hover:text-p200 hover:stroke-p200 md:hidden`}
            onClick={resetAll}
        >
            <AllIcon className={`stroke-white`} />
            <div className={`text-white`}>All</div>
        </button>
    );
};
