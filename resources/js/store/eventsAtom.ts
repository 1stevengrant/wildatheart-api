import { atom } from "recoil";

const eventsListState = atom({
    key: "EventsList",
    default: [],
});

const eventsListLoadingState = atom({
    key: "EventsListLoading",
    default: false,
});

const defaultEventsLoadedState = atom({
    key: "DefaultEventsLoaded",
    default: false,
});

export { eventsListState, eventsListLoadingState, defaultEventsLoadedState };
