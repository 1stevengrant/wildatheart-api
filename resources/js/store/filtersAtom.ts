import { atom } from "recoil";

const typeGroupState = atom({
    key: "TypeGroup",
    default: <string>"",
});
const typesState = atom<number[]>({
    key: "Types",
    default: [],
});
const sexState = atom({
    key: "Sex",
    default: <string>"",
});
const radiusState = atom({
    key: "Radius",
    default: <number>100,
});
const startDateState = atom({
    key: "StartDate",
    default: <Date | null>null,
});
const endDateState = atom({
    key: "EndDate",
    default: <Date | null>null,
});
const startDateStringState = atom({
    key: "StartDateString",
    default: <string>"",
});
const endDateStringState = atom({
    key: "EndDateString",
    default: <string>"",
});

const filterState = atom<{
    sex: string;
    group_type: string;
    event_types: number[];
    start_date: string;
    end_date: string;
}>({
    key: "Filter",
    default: {
        sex: "",
        group_type: "",
        event_types: [],
        start_date: "",
        end_date: "",
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
