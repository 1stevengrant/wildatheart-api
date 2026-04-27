import "../css/app.css";

import "mapbox-gl/dist/mapbox-gl.css";

import React from "react";

import "@mantine/core/styles.css";
import "@mantine/dates/styles.css";
import { createInertiaApp } from "@inertiajs/react";
import { MantineProvider } from "@mantine/core";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import { RecoilRoot } from "recoil";

import { RouteContext } from "@/Hooks/useRoute";
import { initSentry } from "@/lib/sentry";
import { mantineTheme } from "@/mantineTheme";

initSentry();

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
                <RouteContext.Provider value={(window as any).route}>
                    <MantineProvider theme={mantineTheme}>
                        <App {...props} />
                    </MantineProvider>
                </RouteContext.Provider>
            </RecoilRoot>
        );
    },
});
