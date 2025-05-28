import { atom } from "recoil";

export const popupInfoState = atom<App.Data.EventData | null>({
    key: "PopupInfo",
    default: null,
});
