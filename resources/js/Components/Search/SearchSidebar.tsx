import { useRecoilState } from "recoil";

import { EventResult } from "@/Components/Events/Detail/EventResult";
import { NoSearchResults } from "@/Components/NoSearchResults";
import { FiltersPanel } from "@/Components/Search/FiltersPanel";
import { QuickFilters } from "@/Components/Search/QuickFilters";
import { isSelectedEvent } from "@/helpers/isSelectedEvent";
import { showFilterPanelState } from "@/store";
import { eventsListState, currentEventState } from "@/store/eventsAtom";

export const SearchSidebar = () => {
    const [events] = useRecoilState(eventsListState);
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);

    return (
        <aside className="relative flex flex-col rounded-b-2xl bg-black md:w-[557px]">
            {showFilterPanel ? <FiltersPanel /> : <QuickFilters />}
            {!showFilterPanel && (
                <div className="flex-1 overflow-y-auto px-4">
                    {events.length === 0 ? (
                        <NoSearchResults />
                    ) : (
                        events.map((event: App.Data.EventData) => (
                            <EventResult
                                event={event}
                                key={event.id}
                                className={`mb-4 ${isSelectedEvent(event, currentEvent, "border-n400")}`}
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
