import { atom } from "recoil";

export const popupInfoState = atom<App.Data.EventMapData | null>({
    key: "PopupInfo",
    default: null,
});
