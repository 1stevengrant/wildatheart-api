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
import EventCardMobile from "@/Components/Events/EventCardMobile";
import {useSwipeable} from "react-swipeable";
import OutsideClickHandler from "react-outside-click-handler";

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
                    hitsPerPage={2000}
                />
                <MapComponent/>
            </InstantSearch>
            <>
                {currentEvent && (
                    <OutsideClickHandler onOutsideClick={() => setCurrentEvent(undefined)}>
                        <div {...swipeHandlers}>
                            <EventCardMobile className={`fixed bottom-4 right-4 left-4 xl:hidden z-[1001] md:max-w-[500px] md:mx-auto`}/>
                        </div>
                    </OutsideClickHandler>
                )}
            </>
        </AppLayout>
    );
}

export default Home;
