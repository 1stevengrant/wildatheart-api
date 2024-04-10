import { atom } from "recoil";

import { Event } from "@/types/Event";

export const popupInfoState = atom<Event | null>({
    key: "PopupInfo",
    default: null,
});
