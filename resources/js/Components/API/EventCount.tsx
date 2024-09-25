import React from "react";

import { useSetRecoilState } from "recoil";

import { useEventList } from "@/Hooks/useEventList";
import { showFilterPanelState } from "@/store";

export const EventCount = () => {
    const { data } = useEventList();
    const setShowFilterPanel = useSetRecoilState(showFilterPanelState);
    return (
        <button
            onClick={() => setShowFilterPanel(false)}
            className={`bg-white px-4 py-2 text-black inline-block rounded-md font-semibold text-center`}
        >
            {data.length} events found
        </button>
    );
};
