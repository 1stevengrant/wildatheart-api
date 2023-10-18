import AppLayout from "@/Layouts/AppLayout";
import React from "react";
import {
    InstantSearch,
    Configure,
} from 'react-instantsearch-dom';
import {searchClient, indexName} from "@/lib/algoliaClient";
import MapComponent from "@/Components/Maps/MapComponent";
import {currentFireAtom} from "@/store";
import {useRecoilState} from "recoil";
import FirePopupMobile from "@/Components/Fires/FirePopupMobile";
import {useSwipeable} from "react-swipeable";

const Home = () => {
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom)

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
        <AppLayout title={'Home'}>
            <InstantSearch
                searchClient={searchClient}
                indexName={indexName}>
                <Configure
                    analytics={true}
                    hitsPerPage={1000}
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
