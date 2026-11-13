import { useCallback } from "react";

import axios from "axios";
import { LngLatBounds } from "react-map-gl";
import { useRecoilState, useSetRecoilState } from "recoil";

import {
    eventsListLoadingState,
    eventsListState,
    eventsSearchFailedState,
} from "@/store/eventsAtom";
import { featuredContentState } from "@/store/featuredContentAtom";

export interface EventSearchFilters {
    currentSex: string;
    types: number[];
    typeGroup: string;
    startDateString: string;
    endDateString: string;
}

interface EventSearchResponse {
    events: App.Data.EventMapData[];
    featuredContent: App.Data.FeaturedContentData | null;
}

export const useEvents = () => {
    const [showLoading, setShowLoading] = useRecoilState(
        eventsListLoadingState
    );
    const setEvents = useSetRecoilState(eventsListState);
    const setFeaturedContent = useSetRecoilState(featuredContentState);
    const setSearchFailed = useSetRecoilState(eventsSearchFailedState);

    /**
     * Resolves with the number of events found, or null when the request
     * was aborted or failed. An aborted request has been replaced by a newer
     * one, so it leaves the results and the spinner alone.
     */
    const searchEvents = useCallback(
        async (
            bounds: LngLatBounds,
            filters: EventSearchFilters,
            signal: AbortSignal
        ): Promise<number | null> => {
            const ne = bounds.getNorthEast();
            const sw = bounds.getSouthWest();

            setShowLoading(true);

            try {
                const { data } = await axios.get<EventSearchResponse>(
                    route("api.events.search.legacy"),
                    {
                        params: {
                            sw_latitude: sw.lat,
                            sw_longitude: sw.lng,
                            ne_latitude: ne.lat,
                            ne_longitude: ne.lng,
                            sex: filters.currentSex,
                            types: filters.types.join(","),
                            type_group: filters.typeGroup,
                            start_date: filters.startDateString,
                            end_date: filters.endDateString,
                        },
                        signal,
                    }
                );

                setEvents(data.events);
                setFeaturedContent(data.featuredContent);
                setSearchFailed(false);
                setShowLoading(false);

                return data.events.length;
            } catch (error) {
                if (axios.isCancel(error)) {
                    return null;
                }

                setEvents([]);
                setSearchFailed(true);
                setShowLoading(false);

                return null;
            }
        },
        [setShowLoading, setEvents, setFeaturedContent, setSearchFailed]
    );

    return { searchEvents, showLoading };
};
