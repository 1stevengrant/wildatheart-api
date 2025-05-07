import React, { useState, useCallback, useMemo } from "react";

import { Combobox } from "@headlessui/react";
import { MapPinIcon } from "@heroicons/react/24/outline";

import { InputLabel } from "@/Components/Forms/InputLabel";
import { useDebounce } from "@/Hooks/useDebounce";
import { useMapboxSearch } from "@/Hooks/useMapboxSearch";
import { useMapData } from "@/Hooks/useMapData";
import { MapboxPlace } from "@/types/MapboxPlace";

export const MapboxSearch = ({ className = "" }: { className?: string }) => {
    const [selected, setSelected] = useState<MapboxPlace | null>(null);
    const { setSelectedPlace } = useMapData();

    const { queryMapbox, query, setQuery, results, isLoading, error } =
        useMapboxSearch();

    const debouncedQueryMapbox = useDebounce(queryMapbox, 300);

    const handleInputChange = useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            const inputValue = event.target.value;
            setQuery(inputValue);
            debouncedQueryMapbox(inputValue);
        },
        [setQuery, debouncedQueryMapbox]
    );

    const handleSelect = useCallback(
        (place: MapboxPlace) => {
            setSelected(place);
            setSelectedPlace(place);
            setQuery(place.place_name);
        },
        [setSelected, setSelectedPlace, setQuery]
    );

    const inputClassName = useMemo(() => {
        return `w-full border bg-n900 rounded-lg border-transparent py-2 pl-3 text-sm leading-5 text-white placeholder:text-white focus:ring-0 focus:border-transparent ${
            query.trim().length > 0
                ? "rounded-bl-none rounded-br-none focus:border-b-transparent"
                : ""
        }`;
    }, [query]);

    return (
        <div className={`${className} relative w-full z-10`}>
            <InputLabel htmlFor="event_location" className="mb-1">
                Event Location
                <span className={`text-r200 pl-1`}>*</span>
            </InputLabel>
            <Combobox value={selected} onChange={handleSelect}>
                <div className="relative">
                    <div className="relative flex w-full cursor-default items-center rounded-lg border bg-n900 pl-4 border-n400 focus-within:border-p200">
                        <MapPinIcon className="h-5 w-5 text-white" />
                        <Combobox.Input
                            id="event_location"
                            className={inputClassName}
                            displayValue={(place: MapboxPlace | null) =>
                                place ? place.place_name : query
                            }
                            onChange={handleInputChange}
                            autoComplete="off"
                            placeholder="Search for a place"
                        />
                    </div>
                    <Combobox.Options className="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-n900 ring-opacity-5 focus:outline-none sm:text-sm">
                        {isLoading ? (
                            <div className="relative cursor-default select-none px-4 py-2 text-gray-700">
                                Loading...
                            </div>
                        ) : error ? (
                            <div className="relative cursor-default select-none px-4 py-2 text-r200">
                                {error}
                            </div>
                        ) : results.length === 0 && query !== "" ? (
                            <div className="relative cursor-default select-none px-4 py-2 text-gray-700">
                                Nothing found.
                            </div>
                        ) : (
                            results.map((place: MapboxPlace) => (
                                <Combobox.Option
                                    key={place.id}
                                    className={({ active }) =>
                                        `relative cursor-default select-none py-2 pl-10 pr-4 ${
                                            active
                                                ? "bg-p200 text-white"
                                                : "text-gray-900"
                                        }`
                                    }
                                    value={place}
                                >
                                    {({ selected, active }) => (
                                        <>
                                            <span
                                                className={`block truncate ${
                                                    selected
                                                        ? "font-medium"
                                                        : "font-normal"
                                                }`}
                                            >
                                                {place.place_name}
                                            </span>
                                            {selected ? (
                                                <span
                                                    className={`absolute inset-y-0 left-0 flex items-center pl-3 ${
                                                        active
                                                            ? "text-white"
                                                            : "text-p200"
                                                    }`}
                                                >
                                                    <MapPinIcon
                                                        className="h-5 w-5"
                                                        aria-hidden="true"
                                                    />
                                                </span>
                                            ) : null}
                                        </>
                                    )}
                                </Combobox.Option>
                            ))
                        )}
                    </Combobox.Options>
                </div>
            </Combobox>
        </div>
    );
};
