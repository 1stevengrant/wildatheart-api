import { useRecoilState } from "recoil";

import { EventResult } from "@/Components/Events/Detail/EventResult";
import { FiltersPanel } from "@/Components/Search/FiltersPanel";
import { QuickFilters } from "@/Components/Search/QuickFilters";
import { isSelectedEvent } from "@/helpers/isSelectedEvent";
import { currentEventState, showFilterPanelState } from "@/store";
import { eventsListState } from "@/store/eventsAtom";
import { Event } from "@/types/Event";

export const SearchSidebar = () => {
    const [events] = useRecoilState(eventsListState);
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    return (
        <aside className="relative flex flex-col md:w-[557px] rounded-b-2xl bg-black">
            {showFilterPanel ? <FiltersPanel /> : <QuickFilters />}
            {!showFilterPanel && (
                <div className="flex-1 overflow-y-auto px-4">
                    {events.map((event: Event, index: number) => {
                        return (
                            <EventResult
                                event={event}
                                key={index}
                                className={`mb-4 ${isSelectedEvent(event, currentEvent, "border-grey-darker")}`}
                                onMouseEnter={() => setCurrentEvent(event)}
                                onMouseLeave={() => setCurrentEvent(null)}
                            />
                        );
                    })}
                </div>
            )}
        </aside>
    );
};
