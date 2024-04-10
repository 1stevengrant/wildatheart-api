import { MapIcon } from "@heroicons/react/24/outline";
import { useSetRecoilState } from "recoil";

import { fabClasses } from "@/helpers/fabClasses";
import { searchResultsState } from "@/store/searchResultsAtom";

export const ShowMapButton = () => {
    const setSearchResults = useSetRecoilState(searchResultsState);
    return (
        <button
            className={`sticky mx-auto ${fabClasses}`}
            onClick={() => setSearchResults(false)}
        >
            <MapIcon className={`w-6 h-6`} />
            <div className={`flex-shrink-0`}>Show Map</div>
        </button>
    );
};
