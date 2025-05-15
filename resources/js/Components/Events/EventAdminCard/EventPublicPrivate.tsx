export const EventPublicPrivate = ({
    event,
}: {
    event: App.Data.EventData;
}) => {
    return (
        <div
            className={`bg-n700 px-4 rounded-full py-1 font-semibold absolute -top-4`}
        >
            {event.is_private ? "Private" : "Public"}
        </div>
    );
};
