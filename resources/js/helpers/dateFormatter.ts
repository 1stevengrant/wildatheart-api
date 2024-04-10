import dayjs from "dayjs";

export const dateFormatter = (date: string | undefined, format: string) => {
    return dayjs(date).format(format);
};
