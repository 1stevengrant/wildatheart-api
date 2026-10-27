import { useEffect, useRef } from "react";

export const useOnClickOutside = <T extends HTMLElement = HTMLDivElement>(
    onClickOutside: () => void
) => {
    const ref = useRef<T>(null);

    useEffect(() => {
        const handleMouseDown = (event: MouseEvent) => {
            if (ref.current && !ref.current.contains(event.target as Node)) {
                onClickOutside();
            }
        };

        document.addEventListener("mousedown", handleMouseDown);

        return () => document.removeEventListener("mousedown", handleMouseDown);
    }, [onClickOutside]);

    return ref;
};
