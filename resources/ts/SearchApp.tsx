import React from "react";
import {
    InstantSearch,
    Configure,
} from 'react-instantsearch-dom';
import {searchClient, indexName} from "./lib/algoliaClient";
import MapComponent from "./Components/MapComponent";
import MapboxSearch from "./Components/MapboxSearch";
import {currentFireAtom, selectedPlaceAtom, flyToLocationAtom} from "./store";
import {useRecoilState} from "recoil";
import FirePopupMobile from "./Components/FirePopupMobile";

const SearchApp = () => {
    const [currentFire, setCurrentFire] = useRecoilState(currentFireAtom)
    const [selectedPlace, setSelectedPlace] = useRecoilState(selectedPlaceAtom);
    const [flyToLocation, setFlyToLocation] = useRecoilState(flyToLocationAtom)

    return (
        <div className="flex flex-col flex-1">
            <div className={"mx-auto container flex flex-col p-2 md:p-0 md:py-2 md:flex-row md:items-center " + (selectedPlace ? 'space-y-2' +
                ' md:space-y-0 md:space-x-4' : '')}>
                <MapboxSearch onSelect={setSelectedPlace}/>
                {selectedPlace && (
                    <button className="border border-orange px-4 py-2 rounded bg-orange text-white font-semibold"
                            onClick={() => setFlyToLocation(selectedPlace.center)}>
                        Go
                    </button>
                )}
            </div>
            <InstantSearch
                searchClient={searchClient}
                indexName={indexName}>
                <Configure
                    analytics={true}
                    hitsPerPage={400}
                />
                <MapComponent/>
            </InstantSearch>
            {currentFire && (
                <FirePopupMobile className={'fixed bottom-4 right-4 left-4 md:hidden z-[1001]'}/>
            )}

        </div>
    );
}

export default SearchApp;
