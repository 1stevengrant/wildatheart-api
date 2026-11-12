import { atom } from "recoil";

const typeGroupState = atom({
    key: "TypeGroup",
    default: <string>"",
});
const typesState = atom<number[]>({
    key: "Type",
    default: [],
});
const sexState = atom({
    key: "Sex",
    default: <string>"",
});

const startDateState = atom<Date | null>({
    key: "StartDate",
    default: null,
});
const endDateState = atom<Date | null>({
    key: "EndDate",
    default: null,
});

export { typeGroupState, typesState, sexState, startDateState, endDateState };
