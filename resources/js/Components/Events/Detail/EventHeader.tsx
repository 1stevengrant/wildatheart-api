import { Location } from "@/Components/Events/Detail/Location";
import { UnpublishedBadge } from "@/Components/Events/Detail/UnpublishedBadge";

export const EventHeader = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            <div className="flex flex-wrap items-start gap-x-4 gap-y-2 md:flex-nowrap md:items-center">
                <h1 className="mb-1 flex min-w-0 items-center text-3xl leading-9 md:leading-tighter capitalize">
                    {event.display_name}
                </h1>
                {event.badge_display && (
                    <span className="shrink-0 whitespace-nowrap text-base font-normal capitalize bg-white rounded-full text-n900 px-4 py-1">
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
