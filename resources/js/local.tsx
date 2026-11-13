import "../css/app.css";

import "mapbox-gl/dist/mapbox-gl.css";

import React from "react";

import "@mantine/core/styles.css";
import "@mantine/dates/styles.css";
import { createInertiaApp, router } from "@inertiajs/react";
import { MantineProvider } from "@mantine/core";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createRoot } from "react-dom/client";
import { RecoilRoot } from "recoil";

import { initSentry } from "@/lib/sentry";
import { syncSocialMeta } from "@/lib/socialMeta";
import { mantineTheme } from "@/mantineTheme";

initSentry();

// Server-rendered social tags go stale on client-side navigation (no SSR), so
// refresh them on every Inertia visit to keep native sharing accurate.
router.on("navigate", (event) => {
    syncSocialMeta(event.detail.page.props.meta as App.Data.SocialMetaData);
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
                <MantineProvider theme={mantineTheme}>
                    <App {...props} />
                </MantineProvider>
            </RecoilRoot>
        );
    },
});
