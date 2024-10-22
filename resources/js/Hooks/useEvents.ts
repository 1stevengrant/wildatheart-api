import { useState } from "react";

import axios, { CancelTokenSource } from "axios";
import { LngLatBounds } from "react-map-gl";
import { useSetRecoilState } from "recoil";

import { eventsListState } from "@/store/eventsAtom";

export const useEvents = () => {
    const [showLoading, setShowLoading] = useState(false);
    const setEvents = useSetRecoilState(eventsListState);

    const getEvents = async (
        bounds: LngLatBounds,
        currentSex: string,
        types: number[],
        typeGroup: string,
        startDateString: string,
        endDateString: string,
        signal: AbortSignal
    ) => {
        if (bounds) {
            const ne = bounds.getNorthEast();
            const sw = bounds.getSouthWest();
            try {
                setShowLoading(true);

                const source: CancelTokenSource = axios.CancelToken.source();

                signal.addEventListener("abort", () => {
                    source.cancel("Operation canceled by the user.");
                });

                const response = await axios.get(route("api.events.search"), {
                    params: {
                        sw_latitude: sw.lat,
                        sw_longitude: sw.lng,
                        ne_latitude: ne.lat,
                        ne_longitude: ne.lng,
                        sex: currentSex,
                        types: types.join(","),
                        type_group: typeGroup,
                        start_date: startDateString,
                        end_date: endDateString,
                    },
                    cancelToken: source.token,
                });

                setShowLoading(false);
                setEvents(response.data);
                return response.data.length;
            } catch (error) {
                if (axios.isCancel(error)) {
                    console.log("Request canceled:", error.message);
                } else {
                    console.log("Error:", error);
                }
                setShowLoading(false);
                return 0;
            }
        }
        return 0;
    };

    return { getEvents, showLoading };
};
