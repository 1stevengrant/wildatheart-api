import React, { useState, Fragment } from "react";

import { Combobox, Transition } from "@headlessui/react";
import { MapPinIcon } from "@heroicons/react/24/outline";

import InputLabel from "@/Components/Forms/InputLabel";
import { useDebounce } from "@/Hooks/useDebounce";
import { useMapboxSearch } from "@/Hooks/useMapboxSearch";
import { useMapData } from "@/Hooks/useMapData";
import { IMapboxSearchProps } from "@/types/IMapboxSearchProps";
import { MapboxPlace } from "@/types/MapboxPlace";

export const MapboxSearch = ({ className = "" }: IMapboxSearchProps) => {
    const [selected, setSelected] = useState<string>("");
    const { setSelectedPlace, setLongitude, setLatitude } = useMapData();

    const debouncedQueryMapbox = useDebounce((inputValue: string) => {
        if (inputValue.length > 0) {
            // Only call queryMapbox if there's a valid input
            queryMapbox(inputValue).then((r) => setResults(r));
        } else {
            setResults([]); // Clear results if input is empty
        }
    }, 500);

    const { query, queryMapbox, setQuery, setResults, results } =
        useMapboxSearch();

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const inputValue = e.target.value;
        setQuery(inputValue);
        if (inputValue.length > 0) {
            debouncedQueryMapbox(inputValue);
        } else {
            setResults([]); // Clear results if input is cleared or very short
        }
    };

    const handleSelect = (place: any) => {
        setSelectedPlace(place);
        setLatitude(place.center[1]);
        setLongitude(place.center[0]);
        setResults([]);
        setQuery("");
        localStorage.setItem("latitude", place.center[1]);
        localStorage.setItem("longitude", place.center[0]);
    };

    return (
        <div className={className + " relative w-full z-10"}>
            <InputLabel className={`mb-1`}>Event Location</InputLabel>
            <Combobox
                value={selected}
                onChange={(value: string) => {
                    setSelected(value);
                    handleSelect(value);
                }}
            >
                <div className="relative">
                    <div className="relative flex w-full cursor-default items-center rounded-lg border-2 bg-black pl-1.5 border-grey-darker focus-within:border-orange">
                        <MapPinIcon className={`h-5 w-5 text-white`} />
                        <Combobox.Input
                            className={`w-full border-2 bg-black rounded-lg border-transparent py-2 pl-2 text-sm leading-5 text-white placeholder:text-white focus:ring-0 focus:border-transparent ${query.length > 0 ? `rounded-bl-none rounded-br-none focus:border-b-transparent` : ``}`}
                            displayValue={(place: MapboxPlace) =>
                                place.place_name
                            }
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
                        afterLeave={() => setQuery("")}
                    >
                        <Combobox.Options
                            className={
                                "absolute max-h-60 w-full overflow-auto rounded-md bg-white text-base" +
                                " shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm " +
                                (query.length > 0
                                    ? "rounded-tl-none rounded-tr-none"
                                    : "")
                            }
                        >
                            {results.length === 0 && query !== "" ? (
                                <div className="relative cursor-default select-none px-4 py-2 text-black">
                                    Nothing found.
                                </div>
                            ) : (
                                results.map(
                                    (place: MapboxPlace, index: number) => (
                                        <Combobox.Option
                                            key={`place-${index}`}
                                            className={({ active }) =>
                                                `relative cursor-default select-none py-2 px-4 ${
                                                    active
                                                        ? "bg-orange text-white"
                                                        : "text-black"
                                                }`
                                            }
                                            value={place}
                                        >
                                            {({ selected }) => (
                                                <>
                                                    <span
                                                        className={`block ${
                                                            selected
                                                                ? "font-medium"
                                                                : "font-normal"
                                                        }`}
                                                    >
                                                        {place.place_name}
                                                    </span>
                                                </>
                                            )}
                                        </Combobox.Option>
                                    )
                                )
                            )}
                        </Combobox.Options>
                    </Transition>
                </div>
            </Combobox>
        </div>
    );
};
