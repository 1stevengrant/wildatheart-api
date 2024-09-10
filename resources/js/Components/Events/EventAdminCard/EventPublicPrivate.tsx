import { Event } from "@/types/Event";

export const EventPublicPrivate = ({ event }: { event: Event }) => {
    return (
        <div
            className={`bg-grey-darker px-4 rounded-full py-1 font-semibold absolute -top-4`}
        >
            {event.isPrivate ? "Private" : "Public"}
        </div>
    );
};
