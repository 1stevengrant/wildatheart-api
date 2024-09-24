import { useEffect, useState } from "react";

import { router, usePage } from "@inertiajs/react";
import pickBy from "lodash/pickBy";
import { useRecoilState } from "recoil";

import { InertiaToggleGroupType } from "@/Components/InertiaToggleGroupType";
import { InertiaToggleSex } from "@/Components/InertiaToggleSex";
import { ToggleFilterPanelButton } from "@/Components/Search/ToggleFilterPanelButton";
import { eventsListState } from "@/store/eventsAtom";
import { filterState } from "@/store/filtersAtom";
import { useFilterState } from "@/Hooks/useFilterState";

export const QuickFilters = () => {
    const [events] = useRecoilState(eventsListState);
    const { recoilFilters, resetFilters } = useFilterState();
    return (
        <div className="sticky top-0">
            <ToggleFilterPanelButton />
            <div
                className={`border-b border-grey-darker py-2 md:py-6 md:px-4 rounded-b-lg`}
            >
                <div className={`flex items-center justify-center`}>
                    <InertiaToggleGroupType />
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
