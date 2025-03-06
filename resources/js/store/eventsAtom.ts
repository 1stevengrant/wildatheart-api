import { atom } from "recoil";

import { Event } from "@/types/Event";
import { EventType } from "@/types/EventType";

const eventsListState = atom({
    key: "EventsList",
    default: [],
});

const eventsListLoadingState = atom({
    key: "EventsListLoading",
    default: false,
});

const selectedEventTypeState = atom({
    key: "SelectedEventType",
    default: null as EventType | null,
});

const currentEventState = atom<Event | null | undefined>({
    key: "currentEvent",
    default: undefined,
});

export {
    eventsListState,
    eventsListLoadingState,
    selectedEventTypeState,
    currentEventState,
};
