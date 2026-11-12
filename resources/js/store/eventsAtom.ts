import { atom } from "recoil";

const eventsListState = atom<App.Data.EventMapData[]>({
    key: "EventsList",
    default: [],
});

const eventsListLoadingState = atom({
    key: "EventsListLoading",
    default: true,
});

const eventsSearchFailedState = atom({
    key: "EventsSearchFailed",
    default: false,
});

const selectedEventTypeState = atom({
    key: "SelectedEventType",
    default: null as App.Data.EventTypeData | null,
});

const currentEventState = atom<App.Data.EventMapData | null | undefined>({
    key: "currentEvent",
    default: undefined,
});

export {
    eventsListState,
    eventsListLoadingState,
    eventsSearchFailedState,
    selectedEventTypeState,
    currentEventState,
};
