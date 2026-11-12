import type { PageProps } from "@inertiajs/core";
import { usePage } from "@inertiajs/react";

/**
 * usePage() with this page's own props added to the shared props declared
 * in @/types.
 */
export default function useTypedPage<T extends object = object>() {
    return usePage<PageProps & T>();
}
