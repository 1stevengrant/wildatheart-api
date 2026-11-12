import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";

import { latestEventStartDate } from "@/helpers/calendarDate";
import { useDates } from "@/Hooks/useDates";

export const DateFilters = () => {
    const { startDate, setStartDate, endDate, setEndDate, resetAllDates } =
        useDates();

    const handleStartDateChange = (value: Date | null) => {
        if (!value) {
            resetAllDates();

            return;
        }

        setStartDate(value);
    };
    return (
        <div className={`flex flex-col space-y-4`}>
            <div>
                <DatePickerInput
                    label="Start date"
                    placeholder="Select start date"
                    minDate={new Date()}
                    value={startDate}
                    maxDate={latestEventStartDate()}
                    clearable
                    onChange={handleStartDateChange}
                />
            </div>
            <div>
                {startDate && (
                    <DatePickerInput
                        label="End date"
                        placeholder="Select end date"
                        minDate={dayjs(startDate).add(1, "day").toDate()}
                        value={endDate}
                        maxDate={new Date(2100, 11, 31)}
                        clearable
                        onChange={setEndDate}
                    />
                )}
            </div>
        </div>
    );
};
