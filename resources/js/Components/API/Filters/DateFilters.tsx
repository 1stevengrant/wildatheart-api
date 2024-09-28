import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";

import { useDates } from "@/Hooks/useDates";
import { usePopupInfo } from "@/Hooks/usePopupInfo";

export const DateFilters = () => {
    const { maxDate } = useDates();
    const {
        startDate,
        setStartDate,
        setStartDateString,
        endDate,
        setEndDate,
        setEndDateString,
        resetAllDates,
    } = useDates();

    const { setPopupInfo } = usePopupInfo();

    const formatDateString = (date: any) => {
        return dayjs(date).format("YYYY-MM-DD");
    };

    const handleDateChange = (value: Date | null, type = "start") => {
        setPopupInfo(null);
        if (type === "start") {
            setStartDate(value);
            if (value) {
                setStartDateString(formatDateString(value));
            } else {
                resetAllDates();
            }
        } else {
            setEndDate(value);
            if (value) {
                setEndDateString(formatDateString(value));
            } else {
                setEndDateString("");
                setEndDate(null);
            }
        }
    };
    return (
        <div className={`flex flex-col space-y-4`}>
            <div>
                <DatePickerInput
                    label="Start date"
                    placeholder="Select start date"
                    minDate={new Date()}
                    value={startDate}
                    maxDate={maxDate}
                    clearable
                    onChange={(value) => handleDateChange(value)}
                />
            </div>
            <div>
                {startDate && (
                    <DatePickerInput
                        label="End date"
                        placeholder="Select end date"
                        minDate={dayjs(startDate).add(1, "day").toDate()}
                        value={endDate ? new Date(endDate) : null}
                        maxDate={new Date(2100, 11, 31)}
                        clearable
                        onChange={(value) => handleDateChange(value, "end")}
                    />
                )}
            </div>
        </div>
    );
};
