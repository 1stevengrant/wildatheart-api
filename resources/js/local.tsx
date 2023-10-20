import '../css/app.css';
import 'mapbox-gl/dist/mapbox-gl.css';
import {createRoot} from 'react-dom/client';
import {createInertiaApp} from '@inertiajs/react';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {RecoilRoot} from "recoil";
import {RouteContext} from "@/Hooks/useRoute";

const appName = import.meta.env.VITE_APP_NAME || 'Wild at Heart';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    progress: {
        color: '#D65F00',
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx')
        ),
    setup({el, App, props}) {
        const root = createRoot(el);

        root.render(
            <RecoilRoot>
                <RouteContext.Provider value={(window as any).route}>
                    <App {...props} />
                </RouteContext.Provider>
            </RecoilRoot>
        );
    },

});
