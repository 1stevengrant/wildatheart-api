import { useCallback } from "react";

import { useSetRecoilState } from "recoil";

import { reactNotificationState } from "@/store";
import { FlashNotification } from "@/types";

export const useNotify = () => {
    const setReactNotifications = useSetRecoilState(reactNotificationState);

    return useCallback(
        (message: string, type: FlashNotification["type"] = "success") => {
            setReactNotifications((notifications) => [
                ...notifications,
                { message, type },
            ]);
        },
        [setReactNotifications]
    );
};
