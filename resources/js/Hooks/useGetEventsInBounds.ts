import axios from "axios";
import { useRecoilState, useSetRecoilState } from "recoil";

import { useLoaderState } from "@/Hooks/useLoaderState";
import { boundsState } from "@/store/coordinatesAtom";
import { eventsListState } from "@/store/eventsAtom";

export const useGetEventsInBounds = () => {
    const setEvents = useSetRecoilState(eventsListState);
    const { setShowLoader } = useLoaderState();
    const [bounds] = useRecoilState<any>(boundsState);
    const getEventsInBounds = async () => {
        setShowLoader(true);
        const {
            _sw: { lng: swLng, lat: swLat },
            _ne: { lng: neLng, lat: neLat },
        } = bounds;

        try {
            const response = await axios.get("/api/events/search", {
                params: {
                    sw_longitude: swLng,
                    sw_latitude: swLat,
                    ne_longitude: neLng,
                    ne_latitude: neLat,
                },
            });
            setEvents(response.data.data);
            setShowLoader(false);
        } catch (error) {
            console.error("Error fetching events:", error);
        }
    };
    return { getEventsInBounds };
};
