import { usePage } from "@inertiajs/react";

import { PaginatedData } from "@/types";

export const useEventList = () => {
    const { events } = usePage<{
        events: PaginatedData<any>;
    }>().props;

    const { data } = events;

    return { data };
};
