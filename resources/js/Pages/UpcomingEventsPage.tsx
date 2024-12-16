import { usePage } from "@inertiajs/react";

import { EventCard } from "@/Components/Events/EventCard";
import { MobileEventListCard } from "@/Components/Events/MobileEventListCard";
import { UpcomingEventsNav } from "@/Components/Nav/UpcomingEventsNav";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { AppLayout } from "@/Layouts/AppLayout";
import { Event } from "@/types/Event";

interface UpcomingEvents {
    [date: string]: Event[];
}

interface PageProps {
    upcomingEvents: UpcomingEvents;

    [key: string]: any;
}

const UpcomingEventsPage = () => {
    const { upcomingEvents } = usePage<PageProps>().props;
    return (
        <AppLayout title={"Upcoming Events"}>
            <div className={`p-4`}>
                <UpcomingEventsNav />
                <HeadingOne className={`mb-4`}>Upcoming Events</HeadingOne>
                {Object.entries(upcomingEvents).map(([date, events]) => (
                    <div key={date} className={`mb-4`}>
                        <h2 className={`text-lg mb-4`}>{date}</h2>
                        <ul className={`grid md:grid-cols-3 gap-6`}>
                            {events.map((event: any) => (
                                <li
                                    key={event.data.id}
                                    className={`lg:border lg:border-n400 lg:rounded-md lg:hover:border-p200 transition-all ease-in-out`}
                                >
                                    <EventCard
                                        className={`hidden lg:block `}
                                        event={event.data}
                                    />
                                    <ul className={`lg:hidden`}>
                                        <MobileEventListCard
                                            event={event.data}
                                        />
                                    </ul>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
};
export default UpcomingEventsPage;
