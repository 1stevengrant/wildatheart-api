import { useRecoilState } from "recoil";

import { MobileEventList } from "@/Components/API/MobileEventList";
import { MapComponent } from "@/Components/Maps/MapComponent";
import { SearchSidebar } from "@/Components/Search/SearchSidebar";
import { AppLayout } from "@/Layouts/AppLayout";
import { showFilterPanelState } from "@/store";
import { searchResultsState } from "@/store/searchResultsAtom";
import { MobileFiltersPanel } from "@/Components/Search/MobileFiltersPanel";

const HomePage = () => {
    const [searchResults] = useRecoilState(searchResultsState);
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    return (
        <AppLayout
            title={"Events"}
            childClassName={`flex flex-col md:flex-row`}
        >
            {showFilterPanel && <MobileFiltersPanel />}
            <SearchSidebar />
            <MapComponent
                className={`${showFilterPanel ? `hidden` : ``} md:hidden`}
            />
            <MapComponent className={`hidden md:block`} />
            <MobileEventList
                className={`${searchResults ? "block" : "hidden"}`}
            />
        </AppLayout>
    );
};

export default HomePage;
