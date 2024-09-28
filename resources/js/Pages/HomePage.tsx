import React from "react";

import { useRecoilState } from "recoil";

import { MobileEventList } from "@/Components/API/MobileEventList";
import { LocalMap } from "@/Components/Maps/LocalMap";
import { GetUserLocationModal } from "@/Components/Modals/GetUserLocationModal";
import { SearchSidebar } from "@/Components/Search/SearchSidebar";
import { AppLayout } from "@/Layouts/AppLayout";
import { searchResultsState } from "@/store/searchResultsAtom";

const HomePage = ({ children }: { children: React.ReactNode }) => {
    const [searchResults] = useRecoilState(searchResultsState);
    return (
        <AppLayout
            title={"Events"}
            childClassName={`flex flex-col md:flex-row`}
        >
            <GetUserLocationModal />
            <SearchSidebar />
            <LocalMap />
            <MobileEventList
                className={`${searchResults ? "block" : "hidden"}`}
            />
            {children}
        </AppLayout>
    );
};

export default HomePage;
