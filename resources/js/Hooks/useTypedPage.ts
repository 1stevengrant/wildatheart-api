import { usePage } from "@inertiajs/react";

import { InertiaSharedProps } from "@/types";

export default function useTypedPage<T = unknown>() {
    return usePage<InertiaSharedProps<T>>();
}
