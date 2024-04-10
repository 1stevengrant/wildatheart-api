import React from "react";

import { useRecoilState, useSetRecoilState } from "recoil";

import { showFilterPanelState } from "@/store";
import { eventsListState } from "@/store/eventsAtom";

export const EventCount = () => {
    const [events] = useRecoilState(eventsListState);
    const setShowFilterPanel = useSetRecoilState(showFilterPanelState);
    return (
        <button
            onClick={() => setShowFilterPanel(false)}
            className={`bg-white px-4 py-2 text-black inline-block rounded-md font-semibold text-center`}
        >
            {events.length} events found
        </button>
    );
};
