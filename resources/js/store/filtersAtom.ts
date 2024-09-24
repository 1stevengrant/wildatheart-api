import { atom } from "recoil";

const typeGroupState = atom({
    key: "TypeGroupState",
    default: <string>"",
});
const typesState = atom<number[]>({
    key: "TypesState",
    default: [],
});
const sexState = atom({
    key: "SexState",
    default: <string>"",
});
const radiusState = atom({
    key: "RadiusState",
    default: <number>100,
});
const startDateState = atom({
    key: "StartDateState",
    default: <Date | null>null,
});
const endDateState = atom({
    key: "EndDateState",
    default: <Date | null>null,
});
const startDateStringState = atom({
    key: "StartDateStringState",
    default: <string>"",
});
const endDateStringState = atom({
    key: "EndDateStringState",
    default: <string>"",
});

const filterState = atom({
    key: "FilterState",
    default: {
        sex: "",
        type_group: "",
    },
});

export {
    typeGroupState,
    typesState,
    sexState,
    radiusState,
    startDateState,
    endDateState,
    startDateStringState,
    endDateStringState,
    filterState,
};
