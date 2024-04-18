import { useRecoilState } from "recoil";

import { ToggleSex } from "@/Components/API/Toggles/ToggleSex";
import { ToggleTypeGroups } from "@/Components/API/Toggles/ToggleTypeGroups";
import { ToggleFilterPanelButton } from "@/Components/Search/ToggleFilterPanelButton";
import { eventsListState } from "@/store/eventsAtom";

export const QuickFilters = () => {
    const [events] = useRecoilState(eventsListState);
    return (
        <div className="sticky top-0">
            <ToggleFilterPanelButton />
            <div
                className={`border-b border-grey-darker py-2 md:py-6 md:px-4 rounded-b-lg`}
            >
                <div className={`flex items-center justify-center space-x-4`}>
                    <ToggleTypeGroups />
                    <ToggleSex />
                </div>
            </div>
            <div className={`hidden md:block text-center py-4 font-medium`}>
                {events.length > 0 && events.length + " events found"}
            </div>
        </div>
    );
};
