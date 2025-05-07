import { Location } from "@/Components/Events/Detail/Location";
import { UnpublishedBadge } from "@/Components/Events/Detail/UnpublishedBadge";

export const EventHeader = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            <div className="flex items-start md:items-center space-x-4">
                <h1 className="mb-1 flex items-center text-3xl leading-9 md:leading-tighter capitalize">
                    {event.display_name}
                </h1>
                {event.badge_display && (
                    <span className="ml-4 text-base font-normal capitalize bg-white rounded-full text-n900 px-4 py-1">
                        {event.badge_display}
                    </span>
                )}
                {!event.approved_at && <UnpublishedBadge />}
            </div>

            <div className="mt-4 space-y-4 md:space-x-4 md:space-y-0 md:flex md:items-start">
                <Location location={event.location} />
            </div>
        </>
    );
};
