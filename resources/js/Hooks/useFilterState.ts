import { useEffect, useState } from "react";

import { router, usePage } from "@inertiajs/react";
import pickBy from "lodash/pickBy";
import { useRecoilState } from "recoil";

import { filterState } from "@/store/filtersAtom";

export const useFilterState = () => {
    const { filters } = usePage<{
        filters: { sex?: string; group_type?: string; event_types?: number[] };
    }>().props;

    const [filtersFromBackend] = useState({
        sex: filters.sex || "",
        group_type: filters.group_type || "",
        event_types: filters.event_types || [],
    });

    const [recoilFilters, setRecoilFilters] = useRecoilState(filterState);

    useEffect(() => {
        setRecoilFilters(filtersFromBackend);
    }, [filtersFromBackend, setRecoilFilters]);

    useEffect(() => {
        const query = pickBy(recoilFilters);

        router.get(route("home"), query, {
            replace: true,
            preserveState: true,
        });
    }, [recoilFilters]);

    const resetFilters = () =>
        setRecoilFilters({ sex: "", group_type: "", event_types: [] });

    return { recoilFilters, resetFilters };
};
