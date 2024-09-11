export type ISearchEventsParams = {
    latitude: number;
    longitude: number;
    swLatitude: number;
    swLongitude: number;
    neLatitude: number;
    neLongitude: number;
    sex: string;
    typeGroup: string;
    types: Array<number>;
    startDate: string;
    endDate: string;
};
