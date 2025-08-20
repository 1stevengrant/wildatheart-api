import { useRecoilState } from "recoil";

import { ExperiencesIcon } from "@/Components/Icons/ExperiencesIcon";
import { FiresIcon } from "@/Components/Icons/FiresIcon";
import { RetreatsIcon } from "@/Components/Icons/RetreatsIcon";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { currentEventState } from "@/store/eventsAtom";

const generalEventClasses = `w-8 h-8  flex items-center justify-center rounded-full hover:bg-p200 hover:stroke-white shadow-xl hover:shadow-none transition duration-150 ease-in-out`;
const activeEventClasses = "bg-p200 stroke-white";
const inactiveEventClasses = "bg-white stroke-black";
const featuredEventClasses = "ring-2 ring-p200 ring-offset-2";

export const EventMarkerButton = ({ event }: { event: App.Data.EventData }) => {
    const { setPopupInfo } = usePopupInfo();
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);

    return (
        <button
            onMouseEnter={() => setPopupInfo(event)}
            onClick={() => {
                setPopupInfo(event);
                setCurrentEvent(event);
            }}
            className={`${generalEventClasses} ${
                currentEvent?.id === event.id
                    ? activeEventClasses
                    : inactiveEventClasses
            } ${event.is_featured ? featuredEventClasses : ''}`}
        >
            {event.type_group === fireTypeGroup && <FiresIcon />}
            {event.type_group === retreatTypeGroup && <RetreatsIcon />}
            {event.type_group === experienceTypeGroup && <ExperiencesIcon />}
        </button>
    );
};
