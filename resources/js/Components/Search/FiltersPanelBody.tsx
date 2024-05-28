import React from "react";

import { DateFilters } from "@/Components/API/Filters/DateFilters";
import { ExperiencesFilter } from "@/Components/API/Filters/ExperiencesFilter";
import { FiresFilter } from "@/Components/API/Filters/FiresFilter";
import { RetreatsFilter } from "@/Components/API/Filters/RetreatsFilter";
import { ToggleSex } from "@/Components/API/Toggles/ToggleSex";
import { ToggleTypeGroups } from "@/Components/API/Toggles/ToggleTypeGroups";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";

export const FiltersPanelBody = () => {
    return (
        <div className={`overflow-y-scroll px-4 flex-1`}>
            <div className={`mt-2`}>
                <InputLabel>Quick Filters</InputLabel>
                <div className={`flex items-center justify-center space-x-4`}>
                    <ToggleTypeGroups />
                    <ToggleSex />
                </div>
            </div>

            <div className={`mt-4 flex flex-col space-y-4`}>
                <MapboxSearch />
                <DateFilters />
                <div className={`space-y-4`}>
                    <InputLabel>Event Types</InputLabel>
                    <FiresFilter />
                    <RetreatsFilter />
                    <ExperiencesFilter />
                </div>
            </div>
        </div>
    );
};
