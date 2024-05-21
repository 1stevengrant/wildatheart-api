import { atom } from "recoil";

const eventCreateProgressState = atom({
    key: "EventCreateProgressShow",
    default: true,
});

export { eventCreateProgressState };
