import React from "react";

import { useRecoilState } from "recoil";

import { MobileEventList } from "@/Components/API/MobileEventList";
import { MapComponent } from "@/Components/Maps/MapComponent";
import { MobileFiltersPanel } from "@/Components/Search/MobileFiltersPanel";
import { SearchSidebar } from "@/Components/Search/SearchSidebar";
import { AppLayout } from "@/Layouts/AppLayout";
import { showFilterPanelState } from "@/store";
import { searchResultsState } from "@/store/searchResultsAtom";

type HomePageProps = {
    children?: React.ReactNode;
};
const HomePage = ({ children }: HomePageProps) => {
    const [searchResults] = useRecoilState(searchResultsState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);

    return (
        <AppLayout
            title={"Events"}
            childClassName={`flex flex-col md:flex-row`}
        >
            {showFilterPanel && <MobileFiltersPanel />}
            <SearchSidebar />
            {/* mobile map */}
            <MapComponent
                className={`${showFilterPanel ? `hidden` : ``} md:hidden`}
            />
            {/* tablet > map */}
            <MapComponent className={`hidden md:block`} />
            <MobileEventList
                className={`${searchResults ? "block" : "hidden"}`}
            />
            {children}
        </AppLayout>
    );
};

export default HomePage;
