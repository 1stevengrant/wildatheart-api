import React, {useState, useMemo, Fragment} from 'react';
import axios from 'axios';
import debounce from 'lodash.debounce';
import {Combobox, Transition} from '@headlessui/react';
import {MapboxPlace} from "@/types/MapboxPlace";
import {useRecoilState} from "recoil";
import {selectedPlaceAtom} from "@/store";
import {MagnifyingGlassIcon} from "@heroicons/react/20/solid";

interface MapboxSearchProps {
    className?: string;
}

const MapboxSearch = ({className}: MapboxSearchProps) => {
    const [selected, setSelected] = useState<string>('');
    const [query, setQuery] = useState<string>('');
    const [results, setResults] = useState<MapboxPlace[]>([]);
    const [selectedPlace, setSelectedPlace] = useRecoilState(selectedPlaceAtom)

    const debouncedSearch = useMemo(() => debounce(async (searchQuery: string) => {
        if (!searchQuery) return;

        try {
            const response = await axios.get(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(searchQuery)}.json`, {
                params: {
                    access_token: import.meta.env.VITE_REACT_APP_MAPBOX_TOKEN,
                },
            });

            if (response.data && response.data.features) {
                setResults(response.data.features);
            }
        } catch (error) {
            console.error('Error querying Mapbox', error);
        }
    }, 300), []);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const inputValue = e.target.value;
        setQuery(inputValue);
        debouncedSearch(inputValue);
    };

    const handleSelect = (place: string) => {
        setSelectedPlace(place)
        setResults([]);
        setQuery('');
    };

    return (
        <div className={className + ' relative w-full z-10'}>
            <Combobox value={selected}
                      onChange={(value: string) => {
                          setSelected(value)
                          handleSelect(value)
                      }}>
                <div className="relative">
                    <div
                        className="relative w-full cursor-default rounded-lg bg-white flex items-center pl-4 border-2 border-transparent focus-within:border-orange">
                        <MagnifyingGlassIcon className="w-5 h-5 text-black"/>
                        <Combobox.Input
                            className={"w-full border-2 rounded-lg border-transparent py-2 pl-3 text-sm leading-5" +
                                " text-black focus:ring-0 focus:border-transparent " + (query.length > 0 ? "rounded-bl-none rounded-br-none focus:border-b-transparent" : "")}
                            displayValue={(place: MapboxPlace) => place.place_name}
                            autoComplete={"off"}
                            placeholder={"Search for a place"}
                            onChange={(event) => handleInputChange(event)}
                        />
                    </div>
                    <Transition
                        as={Fragment}
                        leave="transition ease-in duration-100"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                        afterLeave={() => setQuery('')}
                    >
                        <Combobox.Options
                            className={"absolute max-h-60 w-full overflow-auto rounded-md bg-white text-base" +
                                " shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm " + (query.length > 0 ? "rounded-tl-none rounded-tr-none" : "")}>
                            {results.length === 0 && query !== '' ? (
                                <div className="relative cursor-default select-none py-2 px-4 text-black">
                                    Nothing found.
                                </div>
                            ) : (
                                results.map((place: MapboxPlace, index: Number) => (
                                    <Combobox.Option
                                        key={`place-${index}`}
                                        className={({active}) =>
                                            `relative cursor-default select-none py-2 px-4 ${
                                                active ? 'bg-orange text-white' : 'text-black'
                                            }`
                                        }
                                        value={place}
                                    >
                                        {({selected, active}) => (
                                            <>
                        <span
                            className={`block ${
                                selected ? 'font-medium' : 'font-normal'
                            }`}
                        >
                          {place.place_name}
                        </span>
                                            </>
                                        )}
                                    </Combobox.Option>
                                ))
                            )}
                        </Combobox.Options>
                    </Transition>
                </div>
            </Combobox>
        </div>
    );
};

export default MapboxSearch;
