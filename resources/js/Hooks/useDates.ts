import { useRecoilState } from "recoil";

import {
    endDateState,
    endDateStringState,
    startDateState,
    startDateStringState,
} from "@/store/filtersAtom";

export const useDates = () => {
    const [startDate, setStartDate] = useRecoilState(startDateState);
    const [endDate, setEndDate] = useRecoilState(endDateState);
    const [startDateString, setStartDateString] =
        useRecoilState(startDateStringState);
    const [endDateString, setEndDateString] =
        useRecoilState(endDateStringState);

    const resetAllDates = () => {
        setStartDate(null);
        setEndDate(null);
        setStartDateString("");
        setEndDateString("");
    };

    return {
        startDate,
        setStartDate,
        endDate,
        setEndDate,
        startDateString,
        setStartDateString,
        endDateString,
        setEndDateString,
        resetAllDates,
    };
};
