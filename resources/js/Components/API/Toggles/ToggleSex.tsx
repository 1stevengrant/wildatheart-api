import React from "react";

import { FemaleIcon } from "@/Components/Icons/FemaleIcon";
import { MaleIcon } from "@/Components/Icons/MaleIcon";
import { EVENT_FOR_MEN } from "@/helpers/eventSex";
import {
    activeQuickFilterClasses,
    inactiveQuickFilterClasses,
    quickFilterButtonClasses,
} from "@/helpers/quickFilterButtonClasses";
import { useDates } from "@/Hooks/useDates";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { useSex } from "@/Hooks/useSex";
import { useTypeGroup } from "@/Hooks/useTypeGroup";
import { useTypes } from "@/Hooks/useTypes";

export const ToggleSex = () => {
    const { setTypesTo, mensEventTypeIds, womensEventTypeIds } = useTypes();
    const { resetAllDates } = useDates();
    const { setPopupInfo } = usePopupInfo();
    const { setTypeGroup } = useTypeGroup();
    const { setCurrentSex, currentSex, sexes } = useSex();

    const handleSexChange = (sex: string) => {
        resetAllDates();
        setPopupInfo(null);
        setTypeGroup("");

        if (currentSex === sex) {
            setCurrentSex(""); // Deselects if the same sex is clicked
            setTypesTo([]); // Optionally clear or reset types here if needed
        } else {
            setCurrentSex(sex);
            // Set the types based on the selected sex
            setTypesTo(
                sex === EVENT_FOR_MEN ? mensEventTypeIds : womensEventTypeIds
            );
        }
    };

    return (
        <>
            {sexes.map((sex: string, index: number) => (
                <div className="flex items-center" key={index}>
                    <button
                        onClick={() => handleSexChange(sex)}
                        className={`${quickFilterButtonClasses} ${currentSex === sex ? activeQuickFilterClasses : inactiveQuickFilterClasses}`}
                    >
                        {sex === EVENT_FOR_MEN ? <MaleIcon /> : <FemaleIcon />}
                        <div>FOR {sex}</div>
                    </button>
                </div>
            ))}
        </>
    );
};
