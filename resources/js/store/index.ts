import { atom } from "recoil";

import { Event } from "@/types/Event";

const currentEventState = atom<Event | null | undefined>({
    key: "currentEvent",
    default: undefined,
});

const reactNotificationState = atom<Array<any>>({
    key: "reactNotification",
    default: [],
});

const showFilterPanelState = atom<boolean>({
    key: "showFilterPanel",
    default: false,
});

export { currentEventState, reactNotificationState, showFilterPanelState };
