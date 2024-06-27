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
        <aside className="relative flex flex-col rounded-b-2xl bg-black md:w-[557px]">
            {showFilterPanel ? <FiltersPanel /> : <QuickFilters />}
            {!showFilterPanel && (
                <div className="flex-1 overflow-y-auto px-4">
                    {events.length === 0 ? (
                        <div className={`py-8`}>
                            <p className="text-white text-center">
                                No events matching your search
                            </p>
                        </div>
                    ) : (
                        events.map((event: Event, index: number) => (
                            <EventResult
                                event={event}
                                key={index}
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
