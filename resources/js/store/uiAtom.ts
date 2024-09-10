import { atom } from "recoil";

const eventCreateProgressState = atom({
    key: "EventCreateProgressShow",
    default: false,
});

export { eventCreateProgressState };
