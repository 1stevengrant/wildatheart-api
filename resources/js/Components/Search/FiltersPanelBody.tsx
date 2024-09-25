import React from "react";

import { DateFilters } from "@/Components/API/Filters/DateFilters";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { InertiaEventTypesFilter } from "@/Components/InertiaEventTypesFilter";
import { InertiaToggleGroupType } from "@/Components/InertiaToggleGroupType";
import { InertiaToggleSex } from "@/Components/InertiaToggleSex";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";

export const FiltersPanelBody = () => {
    return (
        <div className={`overflow-y-scroll px-4 flex-1`}>
            <div className={`mt-2`}>
                <InputLabel>Quick Filters</InputLabel>
                <div className={`flex items-center justify-center space-x-4`}>
                    <InertiaToggleGroupType />
                    <InertiaToggleSex />
                </div>
            </div>

            <div className={`mt-4 flex flex-col space-y-4`}>
                <MapboxSearch />
                <DateFilters />
                <div className={`space-y-4`}>
                    <InputLabel>Event Types</InputLabel>
                    <InertiaEventTypesFilter />
                </div>
            </div>
        </div>
    );
};
