import dayjs from "dayjs";

/**
 * Date pickers work with local midnight Date objects, while the API takes
 * plain YYYY-MM-DD calendar dates. These convert between the two without
 * going through UTC, so the day never shifts with the visitor's timezone.
 */
export const toCalendarDate = (date: Date | null): string | null =>
    date ? dayjs(date).format("YYYY-MM-DD") : null;

export const fromCalendarDate = (value?: string | null): Date | null =>
    value ? dayjs(value.split("T")[0]).toDate() : null;

export const latestEventStartDate = () => dayjs().add(1, "year").toDate();
