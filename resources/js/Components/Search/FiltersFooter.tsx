import { useSetRecoilState } from "recoil";

import { EventCount } from "@/Components/API/EventCount";
import { useSearchEvents } from "@/Hooks/useSearchEvents";
import { showFilterPanelState } from "@/store";

export const FiltersFooter = () => {
    const setShowFilterPanel = useSetRecoilState(showFilterPanelState);
    const { resetAll } = useSearchEvents();
    return (
        <div
            className={`flex items-center justify-between p-4 border-t border-grey-darker`}
        >
            <button
                onClick={() => {
                    setShowFilterPanel(false);
                    resetAll();
                }}
                className={`text-grey-200 px-4 py-2 inline-block rounded-md font-semibold text-center`}
            >
                Show All Events
            </button>
            <EventCount />
        </div>
    );
};
