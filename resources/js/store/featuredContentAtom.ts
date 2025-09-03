import { atom } from "recoil";

export const featuredContentState = atom<App.Data.FeaturedContentData | null>({
    key: "featuredContentState",
    default: null,
});
