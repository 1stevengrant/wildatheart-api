import * as Sentry from "@sentry/react";

const BOT_USER_AGENT_PATTERN =
    /bot|spider|crawl|slurp|baidu|yandex|bingpreview|headless|phantom|puppeteer|prerender|lighthouse/i;

export function initSentry(): void {
    if (
        typeof navigator !== "undefined" &&
        BOT_USER_AGENT_PATTERN.test(navigator.userAgent)
    ) {
        return;
    }

    Sentry.init({
        dsn: import.meta.env.VITE_SENTRY_LARAVEL_DSN,
        maxBreadcrumbs: 50,
        tracesSampleRate: 0,
        profilesSampleRate: 0,
        replaysSessionSampleRate: 0,
        replaysOnErrorSampleRate: 0,
        ignoreErrors: [
            "Non-Error promise rejection captured with value: undefined",
            "Non-Error promise rejection captured with value: null",
            /^Non-Error promise rejection captured with value:/,
            /CustomEvent/,
            "Importing a module script failed",
            "AxiosError: timeout exceeded",
            "AxiosError: Network Error",
            "Network Error",
            /window\.__firefox__/,
            /MetaMask/i,
            /Failed to connect to MetaMask/i,
            "ResizeObserver loop limit exceeded",
            "ResizeObserver loop completed with undelivered notifications",
            "ChunkLoadError",
            /Loading chunk \d+ failed/,
            /Loading CSS chunk/,
            "Failed to fetch dynamically imported module",
            "NetworkError when attempting to fetch resource",
            "The operation was aborted",
            "AbortError",
            /^Event: /,
            "cancelled",
            "canceled",
        ],
        denyUrls: [
            /^chrome-extension:\/\//,
            /^moz-extension:\/\//,
            /^safari-web-extension:\/\//,
            /^safari-extension:\/\//,
            /googletagmanager\.com/,
            /google-analytics\.com/,
            /clarity\.ms/,
            /facebook\.net/,
            /connect\.facebook\.net/,
            /fullstory\.com/,
            /hotjar\.com/,
        ],
        beforeSend(event) {
            const frames =
                event.exception?.values?.[0]?.stacktrace?.frames ?? [];

            const isBrowserExtension = frames.some((frame) =>
                /^(chrome|moz|safari-web|safari)-extension:\/\//.test(
                    frame.filename ?? ""
                )
            );

            if (isBrowserExtension) {
                return null;
            }

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
