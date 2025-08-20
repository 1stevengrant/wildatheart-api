import { useRecoilState } from "recoil";
import { EventCard } from "@/Components/Events/EventCard";
import { eventsListState } from "@/store/eventsAtom";

export const FeaturedEventCard = () => {
    const [events] = useRecoilState(eventsListState);

    // Find the featured event
    const featuredEvent = events.find((event: App.Data.EventData) => event.is_featured);

    if (!featuredEvent) return null;

    return (
        <div className="absolute top-2 right-2 md:left-1/2 md:transform md:-translate-x-1/2 z-50 md:w-[calc(100%-2rem)] sm:w-full md:max-w-[520px] md:px-4 font-sans">
            <div className="relative">
                <EventCard event={featuredEvent} className="shadow-2xl border-p200 border-opacity-50" />
            </div>
        </div>
    );
};
