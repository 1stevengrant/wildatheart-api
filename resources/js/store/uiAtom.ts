import { atom } from "recoil";

const eventCreateProgressState = atom({
    key: "EventCreateProgressShow",
    default: false,
});

const loaderState = atom({
    key: "LoaderState",
    default: false,
});

export { eventCreateProgressState, loaderState };
