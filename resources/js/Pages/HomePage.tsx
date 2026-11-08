import React, { useCallback, useRef } from "react";

import type { MapRef } from "react-map-gl";
import { useRecoilState } from "recoil";

import { MobileEventList } from "@/Components/API/MobileEventList";
import { LocalMap } from "@/Components/Maps/LocalMap";
import { GetUserLocationModal } from "@/Components/Modals/GetUserLocationModal";
import { SearchSidebar } from "@/Components/Search/SearchSidebar";
import { AppLayout } from "@/Layouts/AppLayout";
import { setUserLocation } from "@/lib/localStorage";
import { searchResultsState } from "@/store/searchResultsAtom";

const HomePage = ({ children }: { children: React.ReactNode }) => {
    const [searchResults] = useRecoilState(searchResultsState);
    const mapRef = useRef<MapRef>(null);

    const flyToUserLocation = useCallback(
        (latitude: number, longitude: number) => {
            setUserLocation(latitude, longitude);
            mapRef.current?.getMap().flyTo({
                center: [longitude, latitude],
                zoom: 4,
                essential: true,
            });
        },
        []
    );

    return (
        <AppLayout
            title={"Events"}
            childClassName={`flex flex-col md:flex-row`}
        >
            <GetUserLocationModal onLocationFound={flyToUserLocation} />
            <SearchSidebar />
            <LocalMap mapRef={mapRef} onUserLocated={flyToUserLocation} />
            <MobileEventList
                className={`${searchResults ? "block" : "hidden"}`}
            />
            {children}
        </AppLayout>
    );
};

export default HomePage;
