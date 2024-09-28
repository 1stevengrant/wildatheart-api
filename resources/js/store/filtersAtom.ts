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

const startDateState = atom({
    key: "StartDate",
    default: <Date | null | string | any>null,
});
const endDateState = atom({
    key: "EndDate",
    default: <Date | null | string>null,
});
const startDateStringState = atom({
    key: "StartDateString",
    default: <string>"",
});
const endDateStringState = atom({
    key: "EndDateString",
    default: <string>"",
});

export {
    typeGroupState,
    typesState,
    sexState,
    startDateState,
    endDateState,
    startDateStringState,
    endDateStringState,
};
