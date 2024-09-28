import { atom } from "recoil";

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

export { eventsListState, eventsListLoadingState, selectedEventTypeState };
