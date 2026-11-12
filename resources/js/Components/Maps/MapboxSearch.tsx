import React, { useState, useCallback, useMemo } from "react";

import { Combobox } from "@headlessui/react";
import { MapPinIcon } from "@heroicons/react/24/outline";

import { InputLabel } from "@/Components/Forms/InputLabel";
import { useDebounce } from "@/Hooks/useDebounce";
import { useMapboxSearch } from "@/Hooks/useMapboxSearch";
import { MapboxPlace } from "@/types/MapboxPlace";

interface MapboxSearchProps {
    className?: string;
    onSelect: (place: MapboxPlace) => void;
}

export const MapboxSearch = ({
    className = "",
    onSelect,
}: MapboxSearchProps) => {
    const [selected, setSelected] = useState<MapboxPlace | null>(null);

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
            onSelect(place);
            setQuery(place.place_name);
        },
        [setSelected, onSelect, setQuery]
    );

    const inputClassName = useMemo(() => {
        return `w-full border bg-n900 rounded-lg border-transparent py-2 pl-1 md:pl-2 text-sm leading-5 text-white placeholder:text-white focus:ring-0 focus:border-transparent ${
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
                    <div className="relative flex w-full cursor-default items-center rounded-lg border bg-n900 overflow-hidden pl-2 md:pl-4 border-n400 focus-within:border-p200">
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
                    <Combobox.Options className=" md:text-base text-body-small p-2 md:p-6 absolute mt-1 max-h-72 w-full overflow-auto rounded-md bg-white shadow-lg ring-1 ring-n900 ring-opacity-5 focus:outline-none">
                        {isLoading ? (
                            <div className="relative cursor-default select-none px-5 py-2 text-gray-700">
                                Loading...
                            </div>
                        ) : error ? (
                            <div className="relative cursor-default select-none px-5 py-2 text-r200">
                                {error}
                            </div>
                        ) : results.length === 0 && query !== "" ? (
                            <div className="relative cursor-default select-none px-5 py-2 text-gray-700">
                                Nothing found.
                            </div>
                        ) : (
                            results.map((place: MapboxPlace) => (
                                <Combobox.Option
                                    key={place.id}
                                    className={({ active }) =>
                                        `my-0 relative cursor-default select-none py-2 pl-6 md:pl-5 pr-4 rounded-md ${
                                            active && "bg-p200"
                                        }`
                                    }
                                    value={place}
                                >
                                    {({ selected, active }) => (
                                        <>
                                            <span
                                                className={`md:text-base  text-body-small block truncate align-middle ${selected ? "font-medium" : "font-normal"} ${
                                                    active
                                                        ? "text-white"
                                                        : "text-gray-900"
                                                }`}
                                            >
                                                {place.place_name}
                                            </span>
                                            {selected ? (
                                                <span
                                                    className={`absolute inset-y-0 left-0 flex items-center pl-4 ${
                                                        active
                                                            ? "text-white"
                                                            : "text-p200"
                                                    }`}
                                                >
                                                    <MapPinIcon
                                                        className="h-5 w-5 hidden"
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
