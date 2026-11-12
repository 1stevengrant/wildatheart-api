import { useCallback } from "react";

import { useRecoilState } from "recoil";

import { toCalendarDate } from "@/helpers/calendarDate";
import { endDateState, startDateState } from "@/store/filtersAtom";

/**
 * The home map's date filter. Event forms keep their dates in their own
 * form data instead.
 */
export const useDates = () => {
    const [startDate, setStartDate] = useRecoilState(startDateState);
    const [endDate, setEndDate] = useRecoilState(endDateState);

    const resetAllDates = useCallback(() => {
        setStartDate(null);
        setEndDate(null);
    }, [setStartDate, setEndDate]);

    return {
        startDate,
        endDate,
        startDateString: toCalendarDate(startDate) ?? "",
        endDateString: toCalendarDate(endDate) ?? "",
        resetAllDates,
        setStartDate,
        setEndDate,
    };
};
