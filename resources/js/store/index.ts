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

const reactNotificationAtom = atom<Array<any>>({
    key: "reactNotificationAtom",
    default: [],
});

export {
    currentEventState,
    reactNotificationState,
    showFilterPanelState,
    reactNotificationAtom,
};
