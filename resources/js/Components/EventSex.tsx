import { EVENT_FOR_MEN } from "@/helpers/eventSex";

export const EventSex = ({
    sex,
    className = "",
}: {
    sex?: string;
    className?: string;
}) => {
    return (
        <span className={className}>
            {sex === EVENT_FOR_MEN ? "Men" : "Women"}
        </span>
    );
};
