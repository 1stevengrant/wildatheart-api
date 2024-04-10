import { ListBulletIcon } from "@heroicons/react/24/outline";
import { useRecoilState, useSetRecoilState } from "recoil";

import { fabClasses } from "@/helpers/fabClasses";
import { eventsListState } from "@/store/eventsAtom";
import { searchResultsState } from "@/store/searchResultsAtom";

export const EventCountButton = () => {
    const setSearchResults = useSetRecoilState(searchResultsState);
    const [events] = useRecoilState(eventsListState);
    return (
        <button
            onClick={() => setSearchResults(true)}
            className={`absolute left-1/2 transform -translate-x-1/2 ${fabClasses} md:hidden`}
        >
            <ListBulletIcon className={`w-6 h-6 stroke-black`} />
            <div>Show {events.length} Events</div>
        </button>
    );
};
