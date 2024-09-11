import { useRecoilState, useSetRecoilState } from "recoil";

import { useMapData } from "@/Hooks/useMapData";
import { eventAPIQuery } from "@/lib/eventAPIQuery";
import { eventsListLoadingState, eventsListState } from "@/store/eventsAtom";
import {
    endDateStringState,
    radiusState,
    sexState,
    startDateStringState,
    typeGroupState,
    typesState,
} from "@/store/filtersAtom";

export const useGetEvents = () => {
    const setEvents = useSetRecoilState(eventsListState);
    const setEventsListLoading = useSetRecoilState(eventsListLoadingState);
    const { latitude, longitude, bounds } = useMapData();
    const [radius] = useRecoilState(radiusState);
    const [sex] = useRecoilState(sexState);
    const [typeGroup] = useRecoilState(typeGroupState);
    const [types] = useRecoilState(typesState);
    const [startDate] = useRecoilState(startDateStringState);
    const [endDate] = useRecoilState(endDateStringState);

    const getEvents = async () => {
        try {
            const { swLatitude, swLongitude, neLatitude, neLongitude } = bounds;
            console.log(
                `latitude: ${latitude}, longitude: ${longitude}, bounds: ${bounds}, sex: ${sex}, typeGroup: ${typeGroup}, types: ${types}, startDate: ${startDate}, endDate: ${endDate}`
            );

            setEventsListLoading(true);

            const response = await eventAPIQuery({
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
            });
            setEvents(response.events);
            setEventsListLoading(false);
        } catch (error) {
            console.log(error);
            setEventsListLoading(false);
        }
    };

    return { getEvents };
};
