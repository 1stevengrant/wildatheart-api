import React from "react";

import CloseFilterPanelButton from "@/Components/Search/CloseFilterPanelButton";

export const FiltersPanelHeader = () => {
    return (
        <div
            className={`flex items-center border-b border-grey-darker text-center text-lg font-bold pb-4 px-4`}
        >
            <CloseFilterPanelButton />
            <header className={`w-1/3`}>Filters</header>
            <div className={`w-1/3`} />
        </div>
    );
};
