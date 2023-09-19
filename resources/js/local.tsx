import '../css/app.css';
import 'mapbox-gl/dist/mapbox-gl.css';
import {createRoot} from 'react-dom/client';
import {createInertiaApp} from '@inertiajs/react';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {RecoilRoot} from "recoil";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({el, App, props}) {
        const root = createRoot(el);

        root.render(
            <RecoilRoot>
                <App {...props} />
            </RecoilRoot>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
