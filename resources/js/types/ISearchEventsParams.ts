export type ISearchEventsParams = {
    latitude: number;
    longitude: number;
    radius: number;
    sex: string;
    typeGroup: string;
    types: Array<number>;
    startDate: string;
    endDate: string;
};
