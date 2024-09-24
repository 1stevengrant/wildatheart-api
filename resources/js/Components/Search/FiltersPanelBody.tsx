import React from "react";

import { DateFilters } from "@/Components/API/Filters/DateFilters";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { InertiaTypesFilter } from "@/Components/InertiaTypesFilter";

export const FiltersPanelBody = () => {
    return (
        <div className={`overflow-y-scroll px-4 flex-1`}>
            <div className={`mt-2`}>
                <InputLabel>Quick Filters</InputLabel>
                <div className={`flex items-center justify-center space-x-4`}>
                    {/*<ToggleTypeGroups />*/}
                    {/*<ToggleSex />*/}
                </div>
            </div>

            <div className={`mt-4 flex flex-col space-y-4`}>
                <MapboxSearch />
                <DateFilters />
                <div className={`space-y-4`}>
                    <InputLabel>Event Types</InputLabel>
                    <InertiaTypesFilter />
                </div>
            </div>
        </div>
    );
};
