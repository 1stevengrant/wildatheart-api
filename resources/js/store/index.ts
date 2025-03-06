import { atom } from "recoil";

const reactNotificationState = atom<Array<any>>({
    key: "reactNotification",
    default: [],
});

const showFilterPanelState = atom<boolean>({
    key: "showFilterPanel",
    default: false,
});

export { reactNotificationState, showFilterPanelState };
