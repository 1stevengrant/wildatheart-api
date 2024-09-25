import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
import { useRecoilState } from "recoil";

import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { filterState } from "@/store/filtersAtom";

export const DateFilters = () => {
    const [filters, setFilters] = useRecoilState(filterState);
    const { setPopupInfo } = usePopupInfo();
    const handleStartDateChange = (value: Date | null) => {
        setPopupInfo(null);
        setFilters((prev) => ({
            ...prev,
            start_date: value ? dayjs(value).format("YYYY-MM-DD") : "",
        }));
    };

    const handleEndDateChange = (value: Date | null) => {
        setPopupInfo(null);
        setFilters((prev) => ({
            ...prev,
            end_date: value ? dayjs(value).format("YYYY-MM-DD") : "",
        }));
    };

    return (
        <div className={`flex flex-col space-y-4`}>
            <div>
                <DatePickerInput
                    label="Start date"
                    placeholder="Select start date"
                    minDate={new Date()}
                    value={
                        filters.start_date ? new Date(filters.start_date) : null
                    }
                    maxDate={new Date(2100, 11, 31)}
                    clearable
                    onChange={handleStartDateChange}
                />
            </div>
            <div>
                {filters.start_date && (
                    <DatePickerInput
                        label="End date"
                        placeholder="Select end date"
                        minDate={dayjs(filters.start_date)
                            .add(1, "day")
                            .toDate()}
                        value={
                            filters.end_date ? new Date(filters.end_date) : null
                        }
                        maxDate={new Date(2100, 11, 31)}
                        clearable
                        onChange={handleEndDateChange}
                    />
                )}
            </div>
        </div>
    );
};
