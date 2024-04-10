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
    const { latitude, longitude } = useMapData();
    const [radius] = useRecoilState(radiusState);
    const [sex] = useRecoilState(sexState);
    const [typeGroup] = useRecoilState(typeGroupState);
    const [types] = useRecoilState(typesState);
    const [startDate] = useRecoilState(startDateStringState);
    const [endDate] = useRecoilState(endDateStringState);

    const getEvents = async () => {
        try {
            console.log(
                `latitude: ${latitude}, longitude: ${longitude}, radius: ${radius}, sex: ${sex}, typeGroup: ${typeGroup}, types: ${types}, startDate: ${startDate}, endDate: ${endDate}`
            );

            setEventsListLoading(true);

            const response = await eventAPIQuery({
                latitude,
                longitude,
                radius,
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
