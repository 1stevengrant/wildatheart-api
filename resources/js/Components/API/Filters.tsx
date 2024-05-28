import React from "react";

import { useRecoilState } from "recoil";

import { EventCount } from "@/Components/API/EventCount";
import { DateFilters } from "@/Components/API/Filters/DateFilters";
import { ExperiencesFilter } from "@/Components/API/Filters/ExperiencesFilter";
import { FiresFilter } from "@/Components/API/Filters/FiresFilter";
import { RetreatsFilter } from "@/Components/API/Filters/RetreatsFilter";
import {
    endDateStringState,
    sexState,
    startDateStringState,
    typeGroupState,
} from "@/store/filtersAtom";
import { selectedPlaceState } from "@/store/mapAtom";

export const Filters = () => {
    const [typeGroup] = useRecoilState(typeGroupState);
    const [sex] = useRecoilState(sexState);
    const [startDate] = useRecoilState(startDateStringState);
    const [endDate] = useRecoilState(endDateStringState);
    const [selectedPlace] = useRecoilState(selectedPlaceState);

    return (
        <>
            <EventCount />
            <div className={`mb-4`}>
                {selectedPlace && <div>{selectedPlace.text}</div>}
                <div className={`flex space-x-2`}>
                    {typeGroup && (
                        <div
                            className={`bg-amber-700 text-white text-sm font-medium px-1 py-0.5 rounded`}
                        >
                            {typeGroup}
                        </div>
                    )}
                    {sex && (
                        <div
                            className={`bg-blue-700 text-white text-sm font-medium px-1 py-0.5 rounded`}
                        >
                            {sex}
                        </div>
                    )}
                    {startDate && (
                        <div
                            className={`text-orange text-sm font-medium px-1 py-0.5 rounded`}
                        >
                            Start: {startDate.toString()}
                        </div>
                    )}
                    {endDate && (
                        <div
                            className={`text-orange text-sm font-medium px-1 py-0.5 rounded`}
                        >
                            End: {endDate.toString()}
                        </div>
                    )}
                </div>
                <div className={`grid grid-cols-6 gap-6`}>
                    <FiresFilter />
                    <RetreatsFilter />
                    <ExperiencesFilter />
                    <DateFilters />
                </div>
            </div>
        </>
    );
};
