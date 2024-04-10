import { useState } from "react";

import { useRecoilState } from "recoil";

import { eventsListLoadingState } from "@/store/eventsAtom";

export const useLoaderState = () => {
    const [showLoader, setShowLoader] = useState(false);
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
