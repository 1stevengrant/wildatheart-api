import { Link } from "@inertiajs/react";

export const MyEventsNav = () => {
    return (
        <nav className={`flex items-center space-x-4 my-4`}>
            <Link
                href={route("my.events.active")}
                className={`py-2 border-b-2 hover:border-orange ${route().current("my.events.active") ? "border-white" : "border-transparent"}`}
            >
                Active
            </Link>
            <Link
                href={route("my.events.inactive")}
                className={`py-2 border-b-2 hover:border-orange ${route().current("my.events.inactive") ? "border-white" : "border-transparent"}`}
            >
                Inactive
            </Link>
        </nav>
    );
};
