import { EVENT_FOR_MEN } from "@/helpers/eventSex";

export const EventSex = ({ sex }: { sex?: string }) => {
    return <span>{sex === EVENT_FOR_MEN ? "Men" : "Women"}</span>;
};
