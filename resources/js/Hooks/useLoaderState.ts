import { useRecoilState } from "recoil";

import { eventsListLoadingState } from "@/store/eventsAtom";
import { loaderState } from "@/store/uiAtom";

export const useLoaderState = () => {
    const [showLoader, setShowLoader] = useRecoilState(loaderState);
    const [eventsListLoading] = useRecoilState(eventsListLoadingState);

    const handleLoaderState = () => {
        let timeoutId: number | null = null;

        if (eventsListLoading) {
            setShowLoader(true);
        } else {
            timeoutId = window.setTimeout(() => {
                setShowLoader(false);
            }, 1000);
        }

        return () => {
            if (timeoutId !== null) clearTimeout(timeoutId);
        };
    };

    return { handleLoaderState, setShowLoader, showLoader };
};
