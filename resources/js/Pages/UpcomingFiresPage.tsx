import React, { useState, useMemo, useCallback, useEffect } from "react";

import { usePage } from "@inertiajs/react";

import { EventCard } from "@/Components/Events/EventCard";
import { MobileEventListCard } from "@/Components/Events/MobileEventListCard";
import { UpcomingEventsNav } from "@/Components/Nav/UpcomingEventsNav";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { AppLayout } from "@/Layouts/AppLayout";
import { Event } from "@/types/Event";

const useDebounce = (value: any, delay: any) => {
    const [debouncedValue, setDebouncedValue] = useState(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
};

const UpcomingFiresPage = () => {
    const pageTitle = `Upcoming Fires`;
    const { fires } = usePage().props;

    const upcomingFires = (
        fires as {
            data: Event[];
        }
    ).data;

    const [searchTerm, setSearchTerm] = useState("");
    const debouncedSearchTerm = useDebounce(searchTerm, 300); // 300ms delay

    const filteredFires = useMemo(() => {
        return upcomingFires.filter((event: Event) => {
            const searchRegex = new RegExp(debouncedSearchTerm, "i");
            return (
                searchRegex.test(event.host_name) ||
                searchRegex.test(event.location) ||
                searchRegex.test(event.name)
            );
        });
    }, [upcomingFires, debouncedSearchTerm]);

    const handleSearchChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            setSearchTerm(e.target.value);
        },
        []
    );

    return (
        <AppLayout title={pageTitle}>
            <div className={`p-4`}>
                <UpcomingEventsNav />
                <HeadingOne className={`mb-4`}>{pageTitle}</HeadingOne>
                <div className="mb-4">
                    <input
                        type="search"
                        placeholder="Search fires..."
                        value={searchTerm}
                        onChange={handleSearchChange}
                        className="w-full p-2 border border-grey-darker rounded bg-black text-white placeholder:text-white focus:outline-none focus:border-orange focus:ring-0"
                    />
                </div>
                {debouncedSearchTerm && (
                    <p className="mb-4 text-sm">
                        Found {filteredFires.length} result
                        {filteredFires.length !== 1 ? "s" : ""} for "
                        {debouncedSearchTerm}"
                    </p>
                )}
                {filteredFires.length > 0 ? (
                    <ul className={`grid md:grid-cols-3 gap-6`}>
                        {filteredFires.map((event: Event) => (
                            <li
                                key={event.id}
                                className={`lg:border lg:border-grey-darker lg:rounded-md lg:hover:border-orange transition-all ease-in-out`}
                            >
                                <EventCard
                                    className={`hidden lg:block `}
                                    event={event}
                                />
                                <ul className={`lg:hidden`}>
                                    <MobileEventListCard event={event} />
                                </ul>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <div className="text-center py-8">
                        <p className="text-xl">
                            No fires found matching your search.
                        </p>
                        <p className="text-sm mt-2">
                            Try adjusting your search terms (maybe use country
                            code) or check back later for new Fires.
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default UpcomingFiresPage;
