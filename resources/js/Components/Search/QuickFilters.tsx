import { useEffect, useState } from "react";

import { router, usePage } from "@inertiajs/react";
import pickBy from "lodash/pickBy";
import { useRecoilState } from "recoil";

import { InertiaToggleSex } from "@/Components/InertiaToggleSex";
import { InertiaToggleTypeGroups } from "@/Components/InertiaToggleTypeGroups";
import { ToggleFilterPanelButton } from "@/Components/Search/ToggleFilterPanelButton";
import { eventsListState } from "@/store/eventsAtom";
import { filterState } from "@/store/filtersAtom";

export const QuickFilters = () => {
    const [events] = useRecoilState(eventsListState);
    const { filters } = usePage<{
        filters: { sex?: string; type_group?: string };
    }>().props;

    const [filtersFromBackend] = useState({
        sex: filters.sex || "",
        type_group: filters.type_group || "",
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

    const resetFilters = () => setRecoilFilters({ sex: "", type_group: "" });
    return (
        <div className="sticky top-0">
            <ToggleFilterPanelButton />
            <div
                className={`border-b border-grey-darker py-2 md:py-6 md:px-4 rounded-b-lg`}
            >
                <div className={`flex items-center justify-center`}>
                    <InertiaToggleTypeGroups />
                    <InertiaToggleSex />
                    <button
                        onClick={resetFilters}
                        className="text-sm text-gray-600 hover:text-gray-700 focus:text-indigo-700 focus:outline-none"
                        type="button"
                    >
                        Reset
                    </button>
                </div>
            </div>
            <div className={`hidden md:block text-center py-4 font-medium`}>
                {events.length > 0 && events.length + " events found"}
            </div>
        </div>
    );
};
