import { Event } from "@/types/Event";
export const NoResults = (props: { events: Event[] }) => {
    return (
        <>
            {props.events.length === 0 && (
                <div className="flex items-start justify-center text-center py-2 text-white text-sm">
                    <p>
                        There are no events found in your range, please expand
                        the map view or adjust the filters.
                    </p>
                </div>
            )}
        </>
    );
};
