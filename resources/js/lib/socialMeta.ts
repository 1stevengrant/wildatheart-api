/**
 * The app has no Inertia SSR, so the Open Graph / Twitter tags are only
 * rendered into the document head on the initial full page load. During
 * client-side Inertia navigation those tags are never reissued, leaving them
 * stale, which is what the browser's native share reads (Chrome favours
 * og:title / og:url). This keeps the existing server-rendered tags in sync with
 * the page currently being viewed so a native share always reflects it.
 */
const setMetaContent = (selector: string, value: string | null): void => {
    const element = document.head.querySelector(selector);

    if (element && value !== null) {
        element.setAttribute("content", value);
    }
};

export const syncSocialMeta = (meta?: App.Data.SocialMetaData | null): void => {
    const url = meta?.url ?? window.location.href;
    const title = meta?.title ?? document.title;

    setMetaContent('meta[property="og:url"]', url);
    setMetaContent('meta[property="og:title"]', title);
    setMetaContent('meta[name="twitter:title"]', title);

    if (meta?.description) {
        setMetaContent('meta[name="description"]', meta.description);
        setMetaContent('meta[property="og:description"]', meta.description);
        setMetaContent('meta[name="twitter:description"]', meta.description);
    }

    if (meta?.image) {
        setMetaContent('meta[property="og:image"]', meta.image);
        setMetaContent('meta[name="twitter:image"]', meta.image);
    }

    const canonical = document.head.querySelector('link[rel="canonical"]');

    if (canonical) {
        canonical.setAttribute("href", url);
    }
};
