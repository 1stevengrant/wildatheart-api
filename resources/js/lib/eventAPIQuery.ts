import { ISearchEventsParams } from "@/types/ISearchEventsParams";

export const eventAPIQuery = async ({
    latitude,
    longitude,
    radius,
    sex,
    typeGroup,
    types,
    startDate,
    endDate,
}: ISearchEventsParams): Promise<any> => {
    const query = `/api/events/search?latitude=${latitude}&longitude=${longitude}&radius=${radius}&sex=${sex}&type_group=${typeGroup}&types=${types.join(",")}&start_date=${startDate}&end_date=${endDate}`;
    try {
        const response = await fetch(query);
        if (!response.ok) {
            throw new Error("could not fetch from the api");
        }
        return await response.json();
    } catch (error) {
        console.error(error);
        throw error;
    }
};
