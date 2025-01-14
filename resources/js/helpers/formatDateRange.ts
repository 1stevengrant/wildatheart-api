import { areSameMonth } from "@/helpers/areSameMonth";
import { dateFormatter } from "@/helpers/dateFormatter";

export const formatDateRange = (
    startDate: string,
    endDate?: string
): string => {
    if (!endDate) return dateFormatter(startDate, "MMM Do");

    const startFormatted = dateFormatter(startDate, "MMM Do");
    const endFormat = areSameMonth(startDate, endDate)
        ? "Do YYYY"
        : "MMM Do YYYY";
    const endFormatted = dateFormatter(endDate, endFormat);

    return `${startFormatted} - ${endFormatted}`;
};
