import dayjs from "dayjs";

export const dateFormatter = (
    date: string | undefined,
    format: string = "MMM D"
) => {
    return dayjs(date).format(format);
};
