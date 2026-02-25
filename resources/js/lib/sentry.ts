import * as Sentry from "@sentry/react";

export function initSentry(): void {
    Sentry.init({
        dsn: import.meta.env.VITE_SENTRY_LARAVEL_DSN,
        maxBreadcrumbs: 50,
        ignoreErrors: [
            "Non-Error promise rejection captured with value: undefined",
            "Non-Error promise rejection captured with value: null",
            /^Non-Error promise rejection captured with value:/,
            /CustomEvent/,
            "Importing a module script failed",
            "AxiosError: timeout exceeded",
        ],
        beforeSend(event) {
            const frames =
                event.exception?.values?.[0]?.stacktrace?.frames ?? [];
            const isThirdParty = frames.some(
                (frame) =>
                    frame.filename?.includes("clarity.ms") ||
                    frame.filename?.includes("googletagmanager.com") ||
                    frame.filename?.includes("google-analytics.com") ||
                    frame.filename?.includes("gtag")
            );

            if (isThirdParty) {
                return null;
            }

            if (
                event.exception?.values?.some(
                    (v) =>
                        v.type === "CustomEvent" &&
                        v.value?.includes("unhandledrejection")
                )
            ) {
                return null;
            }

            return event;
        },
    });
}
