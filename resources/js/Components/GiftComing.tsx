import { Link, usePage } from "@inertiajs/react";
import { singular } from "pluralize";

import { HeadingOne } from "@/Components/Type/HeadingOne";
import useRoute from "@/Hooks/useRoute";
import { Event } from "@/types/Event";

export const GiftComing = () => {
    const page = usePage();
    const event = page.props.event as Event;
    const route = useRoute();

    return (
        <div className={"max-w-[500px] mx-auto p-4"}>
            <HeadingOne>
                Yay!
                <br />
                Your gift is on its way!
            </HeadingOne>
            <div className={`mt-8`}>
                <Link
                    href={route("view.event", event)}
                    className="rounded-md border border-transparent px-4 py-2 text-center font-medium text-white transition duration-150 ease-in-out bg-p200 hover:bg-transparent-p200 disabled:bg-n40 disabled:text-grey-darker disabled:cursor-not-allowed"
                >
                    Visit Your {singular(event.event_type.name)} Listing
                </Link>
            </div>
        </div>
    );
};
