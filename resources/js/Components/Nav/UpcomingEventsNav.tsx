import { Link } from "@inertiajs/react";

export const UpcomingEventsNav = () => {
    const linkClass = `block py-2 font-semibold text-grey hover:text-p200 transition-all ease-in-out`;

    return (
        <nav className={`flex space-x-4`}>
            <Link href={route("upcoming.events")} className={`${linkClass}`}>
                Retreats & Experiences
            </Link>
            <Link href={route("upcoming.fires")} className={linkClass}>
                Fires
            </Link>
        </nav>
    );
};
