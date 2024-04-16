import { MagnifyingGlassCircleIcon } from "@heroicons/react/20/solid";
import { useRecoilState } from "recoil";

import { showFilterPanelState } from "@/store";
import { radiusState } from "@/store/filtersAtom";
import { selectedPlaceState } from "@/store/mapAtom";

export const ToggleFilterPanelButton = () => {
    const [selectedPlace] = useRecoilState(selectedPlaceState);
    const [radius] = useRecoilState(radiusState);
    const [showFilterPanel, setShowFilterPanel] =
        useRecoilState(showFilterPanelState);

    const toggleFilterPanelVisibility = () => {
        setShowFilterPanel(!showFilterPanel);
    };

    return (
        <div className={`px-4`}>
            <button
                onClick={() => toggleFilterPanelVisibility()}
                className={`flex border-2 border-white rounded-full w-full `}
            >
                <div
                    className={`w-full mt-2 ml-8 text-sm leading-tight text-left`}
                >
                    <div className={`font-semibold`}>Showing events</div>
                    <div>
                        Within {radius} miles of{" "}
                        {selectedPlace
                            ? selectedPlace?.text
                            : "chosen location"}
                    </div>
                </div>

                <MagnifyingGlassCircleIcon
                    className={`flex-shrink-0 w-14 h-14 rounded-full stroke-1 stroke-white mr-1`}
                />
            </button>
        </div>
    );
};
