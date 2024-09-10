export const EventLocation = ({
    location,
}: {
    location: string | undefined;
}) => {
    return <p className="font-semibold text-white">{location}</p>;
};
