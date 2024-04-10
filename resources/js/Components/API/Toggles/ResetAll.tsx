import React from "react";

import { AllIcon } from "@/Components/Icons/NewIcons/AllIcon";
import { useDates } from "@/Hooks/useDates";
import { useMapData } from "@/Hooks/useMapData";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { useRadius } from "@/Hooks/useRadius";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

export const ResetAll = () => {
    const { resetAllDates } = useDates();
    const { setPopupInfo } = usePopupInfo();
    const { setCurrentSex } = useSex();
    const { resetTypes } = useTypes();
    const { setTypeGroup } = useTypeGroup();
    const { setRadius } = useRadius();
    const { setSelectedPlace } = useMapData();

    return (
        <button
            className={`text-xxs font-medium flex flex-col justify-center items-center md:px-4 md:py-2 rounded-lg md:border-2 uppercase hover:border-orange transition duration-300 ease-in-out cursor-pointer hover:text-orange hover:stroke-orange md:hidden hover:text-orange`}
            onClick={() => {
                setSelectedPlace(null);
                resetAllDates();
                setPopupInfo(null);
                setCurrentSex("");
                setTypeGroup("");
                setRadius(100);
                resetTypes();
            }}
        >
            <AllIcon className={`stroke-white`} />
            <div className={`text-white`}>All</div>
        </button>
    );
};
