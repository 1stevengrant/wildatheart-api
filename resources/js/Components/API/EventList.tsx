import React from "react";

import { usePage } from "@inertiajs/react";
import Tippy from "@tippyjs/react/headless";
import { Marker } from "react-map-gl";
import { useRecoilState } from "recoil";

import { EventCard } from "@/Components/Events/EventCard";
import { EventMarkerButton } from "@/Components/Events/EventMarkerButton";
import { usePopupInfo } from "@/Hooks/usePopupInfo";
import { eventsListState } from "@/store/eventsAtom";
import { Event } from "@/types/Event";

export const EventList = () => {
    const { setPopupInfo } = usePopupInfo();
    const props = usePage().props;
    return (
        <>

        </>
    );
};
