import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";

import { useDates } from "@/Hooks/useDates";
import { usePopupInfo } from "@/Hooks/usePopupInfo";

const DateFilters = () => {
    const maxDate = dayjs().add(1, "year").toDate();
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

    const handleDateChange = (value: Date | null, type = "start") => {
        setPopupInfo(null);
        if (type === "start") {
            setStartDate(value);

            if (value) {
                setStartDateString(value.toISOString().split("T")[0]);
            } else {
                resetAllDates();
            }
        } else {
            setEndDate(value);
            if (value) {
                setEndDateString(value.toISOString().split("T")[0]);
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
                        value={endDate}
                        maxDate={maxDate}
                        clearable
                        onChange={(value) => handleDateChange(value, "end")}
                    />
                )}
            </div>
        </div>
    );
};

export default DateFilters;
