import { ISearchEventsParams } from "@/types/ISearchEventsParams";

export const eventAPIQuery = async ({
    latitude,
    longitude,
    swLatitude,
    swLongitude,
    neLatitude,
    neLongitude,
    sex,
    typeGroup,
    types,
    startDate,
    endDate,
}: ISearchEventsParams): Promise<any> => {
    const query = `/api/v1/events/search?latitude=${latitude}&longitude=${longitude}&sw_latitude=${swLatitude}&sw_longitude=${swLongitude}&ne_latitude=${neLatitude}&ne_longitude=${neLongitude}&sex=${sex}&type_group=${typeGroup}&types=${types.join(",")}&start_date=${startDate}&end_date=${endDate}`;
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
