import React from "react";

import { useRecoilState } from "recoil";

import { MobileEventList } from "@/Components/API/MobileEventList";
import { MapComponent } from "@/Components/Maps/MapComponent";
import { SearchSidebar } from "@/Components/Search/SearchSidebar";
import { AppLayout } from "@/Layouts/AppLayout";
import { searchResultsState } from "@/store/searchResultsAtom";

type HomePageProps = {
    children: React.ReactNode;
};
const HomePage = ({ children }: HomePageProps) => {
    const [searchResults] = useRecoilState(searchResultsState);
    return (
        <AppLayout
            title={"Events"}
            childClassName={`flex flex-col md:flex-row`}
        >
            <SearchSidebar />
            <MapComponent className={`${searchResults ? "hidden" : ""}`} />
            <MobileEventList
                className={`${searchResults ? "block" : "hidden"}`}
            />
            {children}
        </AppLayout>
    );
};

export default HomePage;
