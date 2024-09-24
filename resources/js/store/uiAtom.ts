import { atom } from "recoil";

const loaderState = atom({
    key: "LoaderState",
    default: false,
});

export { loaderState };
