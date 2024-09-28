import { MagnifyingGlassCircleIcon } from "@heroicons/react/20/solid";
import { useRecoilState } from "recoil";

import { showFilterPanelState } from "@/store";

export const ToggleFilterPanelButton = () => {
    const [showFilterPanel, setShowFilterPanel] =
        useRecoilState(showFilterPanelState);

    const toggleFilterPanelVisibility = () => {
        setShowFilterPanel(!showFilterPanel);
    };

    return (
        <div className={`px-4`}>
            <button
                onClick={() => toggleFilterPanelVisibility()}
                className={`flex items-center border-2 border-white rounded-full w-full `}
            >
                <div className={`w-full ml-8 text-sm leading-tight text-left `}>
                    <div className={`font-semibold`}>
                        Showing events from map view
                    </div>
                </div>

                <MagnifyingGlassCircleIcon
                    className={`flex-shrink-0 w-14 h-14 rounded-full stroke-1 mr-1`}
                />
            </button>
        </div>
    );
};
