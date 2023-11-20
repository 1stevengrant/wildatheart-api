import AppLayout from "@/Layouts/AppLayout";
import React from "react";
import {
    InstantSearch,
    Configure,
} from 'react-instantsearch-dom';
import {searchClient, indexName} from "@/lib/algoliaClient";
import MapComponent from "@/Components/Maps/MapComponent";
import {currentEventAtom} from "@/store";
import {useRecoilState} from "recoil";
import EventPopupMobile from "@/Components/Events/EventPopupMobile";
import {useSwipeable} from "react-swipeable";

const Home = () => {
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventAtom)

    const swipeHandlers = useSwipeable({
        onSwipedDown: () => setCurrentEvent(undefined),
        onSwipedUp: () => setCurrentEvent(undefined),
        onSwipedLeft: () => setCurrentEvent(undefined),
        onSwipedRight: () => setCurrentEvent(undefined),
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
                {currentEvent && (
                    <div {...swipeHandlers}>
                        <EventPopupMobile className={'fixed bottom-4 right-4 left-4 md:hidden z-[1001]'}/>
                    </div>
                )}
            </>
        </AppLayout>
    );
}

export default Home;
