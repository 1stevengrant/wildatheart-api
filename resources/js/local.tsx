import "../css/app.css";

import "mapbox-gl/dist/mapbox-gl.css";
import { createRoot } from "react-dom/client";
import * as Sentry from "@sentry/react";
import React from "react";




import "maplibre-gl/dist/maplibre-gl.css";
import "mapbox-gl/dist/mapbox-gl.css";
import "@mantine/core/styles.css";
import "@mantine/dates/styles.css";
import { createInertiaApp } from "@inertiajs/react";
import { MantineProvider } from "@mantine/core";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { RecoilRoot } from "recoil";

import { RouteContext } from "@/Hooks/useRoute";
import { mantineTheme } from "@/mantineTheme";

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
            import.meta.glob("./Pages/**/*.tsx"),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <RecoilRoot>
                <RouteContext.Provider value={(window as any).route}>
                    <MantineProvider theme={mantineTheme}>
                        <App {...props} />
                    </MantineProvider>
                </RouteContext.Provider>
            </RecoilRoot>,
        );
    },
});
