import { XMarkIcon } from "@heroicons/react/24/outline";
import { useSetRecoilState } from "recoil";

import { showFilterPanelState } from "@/store";

export const CloseFilterPanelButton = () => {
    const setShowFilterPanel = useSetRecoilState(showFilterPanelState);
    return (
        <button
            className={`w-1/3`}
            onClick={() => setShowFilterPanel(false)}
            aria-label="Close filters"
        >
            <XMarkIcon className={`w-6 h-6 text-white stroke-white`} />
        </button>
    );
};
