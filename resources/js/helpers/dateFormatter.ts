import dayjs from "dayjs";
import advancedFormat from "dayjs/plugin/advancedFormat"; // ES 2015
dayjs.extend(advancedFormat);

export const dateFormatter = (
    date: string | undefined,
    format: string = "MMM D"
) => {
    return dayjs(date).format(format);
};
