import { useCallback, useEffect, useRef } from "react";

export const useDebounce = <Args extends unknown[]>(
    callback: (...args: Args) => void,
    delay: number
) => {
    const callbackRef = useRef(callback);
    const timeoutRef = useRef<ReturnType<typeof setTimeout> | undefined>(
        undefined
    );

    useEffect(() => {
        callbackRef.current = callback;
    }, [callback]);

    useEffect(() => () => clearTimeout(timeoutRef.current), []);

    return useCallback(
        (...args: Args) => {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setTimeout(
                () => callbackRef.current(...args),
                delay
            );
        },
        [delay]
    );
};
