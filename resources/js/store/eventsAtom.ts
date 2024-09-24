import { atom } from "recoil";

import { EventType } from "@/types/EventType";

const eventsListState = atom({
    key: "EventsListState",
    default: [],
});

const eventsListLoadingState = atom({
    key: "EventsListLoadingState",
    default: false,
});

const defaultEventsLoadedState = atom({
    key: "DefaultEventsLoaded",
    default: false,
});

const selectedEventTypeState = atom({
    key: "SelectedEventType",
    default: null as EventType | null,
});

export {
    eventsListState,
    eventsListLoadingState,
    defaultEventsLoadedState,
    selectedEventTypeState,
};
