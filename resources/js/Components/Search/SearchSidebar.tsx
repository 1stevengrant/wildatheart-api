import { useRecoilState } from "recoil";

import { EventResult } from "@/Components/Events/Detail/EventResult";
import { NoSearchResults } from "@/Components/NoSearchResults";
import { FiltersPanel } from "@/Components/Search/FiltersPanel";
import { QuickFilters } from "@/Components/Search/QuickFilters";
import { isSelectedEvent } from "@/helpers/isSelectedEvent";
import { useEventList } from "@/Hooks/useEventList";
import { currentEventState, showFilterPanelState } from "@/store";

export const SearchSidebar = () => {
    const { data } = useEventList();
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);
    return (
        <aside className="relative flex flex-col rounded-b-2xl bg-black md:w-[557px]">
            {showFilterPanel ? <FiltersPanel /> : <QuickFilters />}
            {!showFilterPanel && (
                <div className="flex-1 overflow-y-auto px-4">
                    {data.length === 0 ? (
                        <NoSearchResults />
                    ) : (
                        data.map((event: any) => (
                            <EventResult
                                event={event}
                                key={event.id}
                                className={`mb-4 ${isSelectedEvent(event, currentEvent, "border-grey-darker")}`}
                                onMouseEnter={() => setCurrentEvent(event)}
                                onMouseLeave={() => setCurrentEvent(null)}
                            />
                        ))
                    )}
                </div>
            )}
        </aside>
    );
};
