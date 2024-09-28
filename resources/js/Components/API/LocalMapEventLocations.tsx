import { Marker } from "react-map-gl";
import { useRecoilState } from "recoil";

import { eventsListState } from "@/store/eventsAtom";

export const LocalMapEventLocations = () => {
    const events = useRecoilState(eventsListState);

    return (
        <>

        </>
    );
};
