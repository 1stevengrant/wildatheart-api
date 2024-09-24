import { atom } from "recoil";

import { Event } from "@/types/Event";

const currentEventState = atom<Event | null | undefined>({
    key: "currentEventState",
    default: undefined,
});

const reactNotificationState = atom<Array<any>>({
    key: "reactNotificationState",
    default: [],
});

const showFilterPanelState = atom<boolean>({
    key: "showFilterPanelState",
    default: false,
});

const reactNotificationAtom = atom<Array<any>>({
    key: "reactNotificationAtomState",
    default: [],
});

export {
    currentEventState,
    reactNotificationState,
    showFilterPanelState,
    reactNotificationAtom,
};
