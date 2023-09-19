import {Head} from '@inertiajs/react';
import AppLayout from "@/Layouts/AppLayout";
import React from "react";
import {
    InstantSearch,
    Configure,
} from 'react-instantsearch-dom';
import {searchClient, indexName} from "@/lib/algoliaClient";
import MapComponent from "@/Components/Maps/MapComponent";
import {currentFireAtom, flyToLocationAtom} from "@/store";
import {useRecoilState} from "recoil";
import FirePopupMobile from "@/Components/Fires/FirePopupMobile";
import {useSwipeable} from "react-swipeable";

const Home = () => {
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom)
    const [flyToLocation, setFlyToLocation] = useRecoilState(flyToLocationAtom)

    const swipeHandlers = useSwipeable({
        onSwipedDown: () => setCurrentFire(undefined),
        onSwipedUp: () => setCurrentFire(undefined),
        onSwipedLeft: () => setCurrentFire(undefined),
        onSwipedRight: () => setCurrentFire(undefined),
        swipeDuration: 500,
        preventScrollOnSwipe: true,
        trackMouse: true
    });

    return (
        <AppLayout>
            <Head title="Home"/>
            <InstantSearch
                searchClient={searchClient}
                indexName={indexName}>
                <Configure
                    analytics={true}
                    hitsPerPage={400}
                />
                <MapComponent/>
            </InstantSearch>
            <>
                {currentFire && (
                    <div {...swipeHandlers}>
                        <FirePopupMobile className={'fixed bottom-4 right-4 left-4 md:hidden z-[1001]'}/>
                    </div>
                )}
            </>
        </AppLayout>
    );
}

export default Home;
