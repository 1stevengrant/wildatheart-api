import React from "react";

import { useRecoilState } from "recoil";

import { ExperiencesIcon } from "@/Components/Icons/NewIcons/ExperiencesIcon";
import { FiresIcon } from "@/Components/Icons/NewIcons/FiresIcon";
import { RetreatsIcon } from "@/Components/Icons/NewIcons/RetreatsIcon";
import {
    experienceTypeGroup,
    fireTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { currentEventState } from "@/store";
import { Event } from "@/types/Event";

const generalEventClasses = `w-8 h-8  flex items-center justify-center rounded-full hover:bg-orange hover:stroke-white shadow-xl hover:shadow-none transition duration-150 ease-in-out`;
const activeEventClasses = "bg-orange stroke-white";
const inactiveEventClasses = "bg-white stroke-black";
export const EventMarkerButton = ({ event }: { event: Event }) => {
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
            }`}
        >
            {event.type_group === fireTypeGroup && <FiresIcon />}
            {event.type_group === retreatTypeGroup && <RetreatsIcon />}
            {event.type_group === experienceTypeGroup && <ExperiencesIcon />}
        </button>
    );
};
