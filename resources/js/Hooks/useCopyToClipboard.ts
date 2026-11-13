import { useCallback } from "react";

import { useNotify } from "@/Hooks/useNotify";

export const useCopyToClipboard = () => {
    const notify = useNotify();

    return useCallback(
        async (text: string): Promise<boolean> => {
            try {
                await navigator.clipboard.writeText(text);
                notify("URL copied to clipboard!");

                return true;
            } catch {
                notify("Oops, unable to copy", "error");

                return false;
            }
        },
        [notify]
    );
};
