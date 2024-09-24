import "../css/app.css";

import "mapbox-gl/dist/mapbox-gl.css";

import React, { useEffect } from "react";

import "maplibre-gl/dist/maplibre-gl.css";
import "mapbox-gl/dist/mapbox-gl.css";
import "@mantine/core/styles.css";
import "@mantine/dates/styles.css";
import { createInertiaApp } from "@inertiajs/react";
import { MantineProvider } from "@mantine/core";
import * as Sentry from "@sentry/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import { RecoilRoot, useRecoilSnapshot } from "recoil";

import { RouteContext } from "@/Hooks/useRoute";
import { mantineTheme } from "@/mantineTheme";

function DebugObserver(): React.ReactNode {
    const snapshot = useRecoilSnapshot();
    useEffect(() => {
        console.debug("The following atoms were modified:");
        for (const node of snapshot.getNodes_UNSTABLE({ isModified: true })) {
            console.debug(node.key, snapshot.getLoadable(node));
        }
    }, [snapshot]);

    return null;
}

Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_LARAVEL_DSN,
    maxBreadcrumbs: 50,
    debug: true,
});

const appName = import.meta.env.VITE_APP_NAME || "Wild at Heart";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    progress: {
        color: "#D65F00",
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob("./Pages/**/*.tsx")
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <RecoilRoot>
                <DebugObserver />
                <RouteContext.Provider value={(window as any).route}>
                    <MantineProvider theme={mantineTheme}>
                        <App {...props} />
                    </MantineProvider>
                </RouteContext.Provider>
            </RecoilRoot>
        );
    },
});
