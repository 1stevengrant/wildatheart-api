import { EventCard } from "@/Components/Events/EventCard";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { AppLayout } from "@/Layouts/AppLayout";

const UpcomingEventsPage = ({
    upcomingEvents,
}: {
    upcomingEvents: Record<string, App.Data.EventData[]>;
}) => {
    return (
        <AppLayout title={"Upcoming Events"}>
            <div className={`p-4`}>
                <HeadingOne className={`mb-4`}>Upcoming Events</HeadingOne>

                {Object.entries(upcomingEvents).map(([monthYear, events]) => (
                    <div key={monthYear} className="mb-8">
                        {/* Month/Year Header */}
                        <h2 className="text-2xl font-bold mb-4 text-white">
                            {monthYear}
                        </h2>

                        {/* Events for this month */}
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {events.map((event) => (
                                <div
                                    key={event.id}
                                    className="bg-n800 p-1 rounded-lg"
                                >
                                    <EventCard event={event} />
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
};
export default UpcomingEventsPage;
