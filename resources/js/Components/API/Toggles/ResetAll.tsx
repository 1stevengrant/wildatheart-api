import React from "react";

import { AllIcon } from "@/Components/Icons/AllIcon";
import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

export const ResetAll = () => {
    const { resetAllDates } = useDates();
    const { setPopupInfo } = usePopupInfo();
    const { setCurrentSex } = useSex();
    const { resetTypes } = useTypes();
    const { setTypeGroup } = useTypeGroup();
    const { setSelectedPlace } = useMapData();

    return (
        <button
            className={`text-xxs font-medium flex flex-col justify-center items-center md:px-4 md:py-2 rounded-lg md:border-2 uppercase hover:border-p200 transition duration-300 ease-in-out cursor-pointer hover:text-p200 hover:stroke-p200 md:hidden`}
            onClick={() => {
                setSelectedPlace(null);
                resetAllDates();
                setPopupInfo(null);
                setCurrentSex("");
                setTypeGroup("");
                resetTypes();
            }}
        >
            <AllIcon className={`stroke-white`} />
            <div className={`text-white`}>All</div>
        </button>
    );
};
