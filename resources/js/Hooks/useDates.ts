import { useCallback } from "react";

import dayjs from "dayjs";
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
    const maxDate = dayjs().add(1, "year").toDate();

    const resetAllDates = useCallback(() => {
        setStartDate(null);
        setEndDate(null);
        setStartDateString("");
        setEndDateString("");
    }, [setStartDate, setEndDate, setStartDateString, setEndDateString]);

    const convertDateToIsoString = useCallback(
        (date: Date | null): string | null => {
            if (!date) return null;

            const year = date.getFullYear();
            const month = date.getMonth();
            const day = date.getDate();

            const adjustedDate = new Date(Date.UTC(year, month, day, 0, 0, 0));

            return adjustedDate.toISOString().split("T")[0];
        },
        []
    );

    const handleDateChange = useCallback(
        (
            date: Date | null,
            setDate: (date: Date | null) => void,
            setDateString: (str: string) => void,
            fieldName: string,
            setData: (field: string, value: unknown) => void
        ) => {
            setDate(date);

            if (date) {
                const iso = convertDateToIsoString(date);
                setDateString(iso || "");
                setData(fieldName, iso);
            } else {
                setData(fieldName, null);
                resetAllDates();
            }
        },
        [convertDateToIsoString, resetAllDates]
    );

    const handleStartDateChange = useCallback(
        (
            date: Date | null,
            setData: (field: string, value: unknown) => void
        ) => {
            handleDateChange(
                date,
                setStartDate,
                setStartDateString,
                "start_date",
                setData
            );
        },
        [handleDateChange, setStartDate, setStartDateString]
    );

    const handleEndDateChange = useCallback(
        (
            date: Date | null,
            setData: (field: string, value: unknown) => void
        ) => {
            handleDateChange(
                date,
                setEndDate,
                setEndDateString,
                "end_date",
                setData
            );
        },
        [handleDateChange, setEndDate, setEndDateString]
    );

    return {
        startDate,
        endDate,
        startDateString,
        endDateString,
        maxDate,
        resetAllDates,
        handleStartDateChange,
        handleEndDateChange,
        setStartDate,
        setEndDate,
        setStartDateString,
        setEndDateString,
    };
};
