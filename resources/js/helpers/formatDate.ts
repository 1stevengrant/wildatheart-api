import dayjs from "dayjs";

export const formatDate = (dateString: string | null | undefined): string => {
    if (!dateString) return "";

    try {
        const date = dayjs(dateString);
        const currentYear = dayjs().year();
        const dateYear = date.year();

        const day = date.date();
        const month = date.format("MMM");

        // Add ordinal suffix (st, nd, rd, th)
        const getOrdinalSuffix = (day: number): string => {
            if (day >= 11 && day <= 13) return "th";
            switch (day % 10) {
                case 1:
                    return "st";
                case 2:
                    return "nd";
                case 3:
                    return "rd";
                default:
                    return "th";
            }
        };

        const formattedDate = `${month} ${day}${getOrdinalSuffix(day)}`;

        // Only show year if it's different from current year
        return dateYear === currentYear
            ? formattedDate
            : `${formattedDate}, ${dateYear}`;
    } catch (error) {
        return dateString;
    }
};
