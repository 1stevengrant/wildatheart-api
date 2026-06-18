import { useRecoilState } from "recoil";

import { EventResult } from "@/Components/Events/Detail/EventResult";
import { FeaturedContentCard } from "@/Components/Maps/FeaturedContentCard";
import { NoSearchResults } from "@/Components/NoSearchResults";
import { FiltersPanel } from "@/Components/Search/FiltersPanel";
import { QuickFilters } from "@/Components/Search/QuickFilters";
import { IsLoading } from "@/Components/Ui/IsLoading";
import { isSelectedEvent } from "@/helpers/isSelectedEvent";
import { showFilterPanelState } from "@/store";
import {
    eventsListState,
    eventsListLoadingState,
    currentEventState,
} from "@/store/eventsAtom";
import { featuredContentState } from "@/store/featuredContentAtom";

export const SearchSidebar = () => {
    const [events] = useRecoilState(eventsListState);
    const [isLoading] = useRecoilState(eventsListLoadingState);
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    const [featuredContent] = useRecoilState(featuredContentState);
    return (
        <aside className="relative flex flex-col rounded-b-2xl bg-black md:w-[557px]">
            {showFilterPanel ? <FiltersPanel /> : <QuickFilters />}
            {!showFilterPanel && (
                <div className="flex-1 overflow-y-auto">
                    {/* Desktop only - featured content sticky at top */}
                    <div className="hidden md:block sticky top-0 z-10 px-4 bg-black">
                        <FeaturedContentCard
                            featuredContent={featuredContent}
                            position="sidebar"
                        />
                    </div>

                    <div className="px-4">
                        {isLoading ? (
                            <IsLoading
                                className={`py-8`}
                                text={`Loading Events`}
                            />
                        ) : events.length === 0 ? (
                            <NoSearchResults />
                        ) : (
                            events.map((event: any) => (
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
                </div>
            )}
        </aside>
    );
};
