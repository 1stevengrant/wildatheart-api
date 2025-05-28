import { useEffect } from "react";

export const useMobileLayout = (): void => {
    useEffect(() => {
        if (window.innerWidth < 768) {
            const eventElement = document.getElementById("event");
            const connectWithHostElement = document.getElementById(
                "mobile-connect-with-host"
            );

            if (!eventElement || !connectWithHostElement) return;

            eventElement.style.paddingBottom = `${connectWithHostElement.offsetHeight + 16}px`;
        }
    }, []);
};
