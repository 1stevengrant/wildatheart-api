import React, { useState, useCallback } from "react";

import { Combobox } from "@headlessui/react";
import { MapPinIcon } from "@heroicons/react/24/outline";
import { router, usePage } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";

import Pagination from "@/Components/Pagination";
import { useDebounce } from "@/Hooks/useDebounce";
import { useMapboxSearch } from "@/Hooks/useMapboxSearch";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { PaginatedData } from "@/types";
import { MapboxPlace } from "@/types/MapboxPlace";

type EventSearchResult = {
    id: string;
    name: string | null;
    host_name: string | null;
    host_email: string | null;
    host_phone: string | null;
    city: string | null;
    state: string | null;
    event_type: string | null;
    frequency: string | null;
    attendee_count: number | null;
    start_date: string | null;
    distance: number | null;
    status: string;
    host_street: string | null;
    host_address_city: string | null;
    host_address_state: string | null;
    host_zip: string | null;
    host_country: string | null;
    created_at: string | null;
};

type AvailableField = {
    key: string;
    label: string;
    default: boolean;
};

type EventType = {
    id: number;
    name: string;
};

type PageProps = {
    results: PaginatedData<EventSearchResult>;
    filters: {
        lat: number | null;
        lng: number | null;
        radius: number;
        event_type_ids: number[];
        fields: string[];
        place_name: string;
        active_only: boolean;
        include_deleted: boolean;
        created_from: string | null;
        created_to: string | null;
    };
    availableFields: AvailableField[];
    eventTypes: EventType[];
};

const EventSearchPage = () => {
    const { results, filters, availableFields, eventTypes } =
        usePage<PageProps>().props;

    const [selectedPlace, setSelectedPlace] = useState<MapboxPlace | null>(
        null
    );
    const [placeName, setPlaceName] = useState(filters.place_name || "");
    const [radius, setRadius] = useState(filters.radius || 30);
    const [selectedEventTypeIds, setSelectedEventTypeIds] = useState<number[]>(
        filters.event_type_ids || []
    );
    const [selectedFields, setSelectedFields] = useState<string[]>(
        filters.fields.length > 0
            ? filters.fields
            : availableFields.filter((f) => f.default).map((f) => f.key)
    );
    const [activeOnly, setActiveOnly] = useState(filters.active_only ?? true);
    const [includeDeleted, setIncludeDeleted] = useState(
        filters.include_deleted ?? false
    );
    const [createdFrom, setCreatedFrom] = useState<Date | null>(
        filters.created_from
            ? new Date(filters.created_from + "T00:00:00")
            : null
    );
    const [createdTo, setCreatedTo] = useState<Date | null>(
        filters.created_to ? new Date(filters.created_to + "T00:00:00") : null
    );

    const [hasSearched, setHasSearched] = useState(
        window.location.search.length > 1
    );
    const [isSearching, setIsSearching] = useState(false);
    const [searchError, setSearchError] = useState<string | null>(null);
    const [toast, setToast] = useState<{
        message: string;
        type: "success" | "error";
    } | null>(null);

    const {
        queryMapbox,
        query,
        setQuery,
        results: searchResults,
        isLoading,
    } = useMapboxSearch();

    const debouncedQueryMapbox = useDebounce(queryMapbox, 300);

    const handleInputChange = useCallback(
        (event: React.ChangeEvent<HTMLInputElement>) => {
            const inputValue = event.target.value;
            setQuery(inputValue);
            setPlaceName(inputValue);
            debouncedQueryMapbox(inputValue);
        },
        [setQuery, debouncedQueryMapbox]
    );

    const handlePlaceSelect = useCallback(
        (place: MapboxPlace) => {
            setSelectedPlace(place);
            setQuery(place.place_name);
            setPlaceName(place.place_name);
        },
        [setQuery]
    );

    const handleEventTypeToggle = (typeId: number) => {
        setSelectedEventTypeIds((prev) =>
            prev.includes(typeId)
                ? prev.filter((id) => id !== typeId)
                : [...prev, typeId]
        );
    };

    const handleFieldToggle = (fieldKey: string) => {
        setSelectedFields((prev) =>
            prev.includes(fieldKey)
                ? prev.filter((f) => f !== fieldKey)
                : [...prev, fieldKey]
        );
    };

    const handleSearch = () => {
        setHasSearched(true);
        setIsSearching(true);
        setSearchError(null);
        router.get(
            "/internal/event-search",
            {
                lat: selectedPlace?.center[1] ?? null,
                lng: selectedPlace?.center[0] ?? null,
                radius: selectedPlace ? radius : null,
                event_type_ids: selectedEventTypeIds,
                fields: selectedFields,
                place_name: placeName,
                active_only: activeOnly,
                include_deleted: includeDeleted,
                created_from: createdFrom
                    ? createdFrom.toISOString().split("T")[0]
                    : null,
                created_to: createdTo
                    ? createdTo.toISOString().split("T")[0]
                    : null,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setIsSearching(false),
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat();
                    setSearchError(
                        errorMessages.length > 0
                            ? errorMessages.join(", ")
                            : "An error occurred while searching. Please try again."
                    );
                },
            }
        );
    };

    const handleReset = () => {
        setSelectedPlace(null);
        setQuery("");
        setPlaceName("");
        setRadius(30);
        setSelectedEventTypeIds([]);
        setSelectedFields(
            availableFields.filter((f) => f.default).map((f) => f.key)
        );
        setActiveOnly(true);
        setIncludeDeleted(false);
        setCreatedFrom(null);
        setCreatedTo(null);
        setSearchError(null);
        setHasSearched(false);
        router.visit("/internal/event-search", {
            preserveState: false,
            replace: true,
        });
    };

    const showToast = (message: string, type: "success" | "error") => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 5000);
    };

    const handleExport = () => {
        router.get(
            "/internal/event-search/export",
            {
                lat: filters.lat,
                lng: filters.lng,
                radius: filters.lat && filters.lng ? radius : null,
                active_only: activeOnly,
                include_deleted: includeDeleted,
                event_type_ids: selectedEventTypeIds,
                fields: selectedFields,
                created_from: createdFrom
                    ? createdFrom.toISOString().split("T")[0]
                    : null,
                created_to: createdTo
                    ? createdTo.toISOString().split("T")[0]
                    : null,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    showToast(
                        "Your export is being generated. You will receive an email shortly.",
                        "success"
                    );
                },
                onError: () => {
                    showToast(
                        "Failed to start export. Please try again.",
                        "error"
                    );
                },
            }
        );
    };

    const getFieldValue = (
        result: EventSearchResult,
        field: string
    ): string => {
        const value = result[field as keyof EventSearchResult];
        if (value === null || value === undefined) {
            return "";
        }
        if (field === "distance") {
            return `${value} mi`;
        }
        return String(value);
    };

    return (
        <EmptyLayout title="Event Search">
            {/* Toast Notification */}
            {toast && (
                <div className="fixed top-4 right-4 z-50 animate-in fade-in slide-in-from-top-2 duration-300">
                    <div
                        className={`px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 ${
                            toast.type === "success"
                                ? "bg-green-600 text-white"
                                : "bg-red-600 text-white"
                        }`}
                    >
                        {toast.type === "success" ? (
                            <svg
                                className="h-5 w-5 flex-shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        ) : (
                            <svg
                                className="h-5 w-5 flex-shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        )}
                        <span className="text-sm font-medium">
                            {toast.message}
                        </span>
                        <button
                            onClick={() => setToast(null)}
                            className="ml-2 hover:opacity-80"
                        >
                            <svg
                                className="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            )}

            <div className="p-4 max-w-7xl mx-auto">
                <h1 className="text-2xl font-bold text-white mb-6">
                    Event Search
                </h1>

                {/* Search Form */}
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Location Search */}
                            <div className="relative z-20">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Location{" "}
                                    <span className="text-gray-400 font-normal">
                                        (optional - leave empty for all)
                                    </span>
                                </label>
                                <Combobox
                                    value={selectedPlace}
                                    onChange={handlePlaceSelect}
                                >
                                    <div className="relative">
                                        <div className="relative flex w-full cursor-default items-center rounded-lg border bg-white overflow-hidden pl-3 border-gray-300 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500">
                                            <MapPinIcon className="h-5 w-5 text-gray-400" />
                                            <Combobox.Input
                                                className="w-full border-none py-2 pl-2 text-sm leading-5 text-gray-900 focus:ring-0"
                                                displayValue={(
                                                    place: MapboxPlace | null
                                                ) =>
                                                    place
                                                        ? place.place_name
                                                        : query
                                                }
                                                onChange={handleInputChange}
                                                autoComplete="off"
                                                placeholder="Search for a city or address..."
                                            />
                                        </div>
                                        <Combobox.Options className="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none text-sm">
                                            {isLoading ? (
                                                <div className="relative cursor-default select-none px-4 py-2 text-gray-700">
                                                    Loading...
                                                </div>
                                            ) : searchResults.length === 0 &&
                                              query !== "" ? (
                                                <div className="relative cursor-default select-none px-4 py-2 text-gray-700">
                                                    Nothing found.
                                                </div>
                                            ) : (
                                                searchResults.map(
                                                    (place: MapboxPlace) => (
                                                        <Combobox.Option
                                                            key={place.id}
                                                            className={({
                                                                active,
                                                            }) =>
                                                                `relative cursor-default select-none py-2 pl-10 pr-4 ${
                                                                    active
                                                                        ? "bg-blue-600 text-white"
                                                                        : "text-gray-900"
                                                                }`
                                                            }
                                                            value={place}
                                                        >
                                                            {({ selected }) => (
                                                                <span
                                                                    className={`block truncate ${
                                                                        selected
                                                                            ? "font-medium"
                                                                            : "font-normal"
                                                                    }`}
                                                                >
                                                                    {
                                                                        place.place_name
                                                                    }
                                                                </span>
                                                            )}
                                                        </Combobox.Option>
                                                    )
                                                )
                                            )}
                                        </Combobox.Options>
                                    </div>
                                </Combobox>
                            </div>

                            {/* Radius Input */}
                            {(selectedPlace ||
                                (filters.lat && filters.lng)) && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Radius (miles)
                                    </label>
                                    <input
                                        type="number"
                                        value={radius}
                                        onChange={(e) =>
                                            setRadius(Number(e.target.value))
                                        }
                                        min={1}
                                        max={500}
                                        className="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                            )}
                        </div>

                        {/* Filter Toggles */}
                        <div className="mt-6 flex flex-wrap gap-6">
                            <label className="flex items-center space-x-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={activeOnly}
                                    onChange={(e) =>
                                        setActiveOnly(e.target.checked)
                                    }
                                    className="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5"
                                />
                                <span className="text-sm font-medium text-gray-700">
                                    Published only
                                </span>
                            </label>
                            <label className="flex items-center space-x-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={includeDeleted}
                                    onChange={(e) =>
                                        setIncludeDeleted(e.target.checked)
                                    }
                                    className="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5"
                                />
                                <span className="text-sm font-medium text-gray-700">
                                    Include deleted
                                </span>
                            </label>
                        </div>

                        {/* Created Between Date Filters */}
                        <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <DatePickerInput
                                className="[&>.mantine-DatePickerInput-label]:text-black"
                                label="Created from"
                                placeholder="Select start date"
                                value={createdFrom}
                                onChange={setCreatedFrom}
                                clearable
                            />
                            <DatePickerInput
                                className="[&>.mantine-DatePickerInput-label]:text-black"
                                label="Created to"
                                placeholder="Select end date"
                                value={createdTo}
                                onChange={setCreatedTo}
                                clearable
                            />
                        </div>

                        {/* Event Type Selection */}
                        <div className="mt-6">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Event Types{" "}
                                <span className="text-gray-400 font-normal">
                                    (leave empty for all)
                                </span>
                            </label>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                {eventTypes.map((type) => (
                                    <label
                                        key={type.id}
                                        className="flex items-center space-x-2 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={selectedEventTypeIds.includes(
                                                type.id
                                            )}
                                            onChange={() =>
                                                handleEventTypeToggle(type.id)
                                            }
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm text-gray-700">
                                            {type.name}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>

                        {/* Field Selection */}
                        <div className="mt-6">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Export Fields
                            </label>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                {availableFields.map((field) => (
                                    <label
                                        key={field.key}
                                        className="flex items-center space-x-2 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={selectedFields.includes(
                                                field.key
                                            )}
                                            onChange={() =>
                                                handleFieldToggle(field.key)
                                            }
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm text-gray-700">
                                            {field.label}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>

                        {/* Search Buttons */}
                        <div className="mt-6 flex gap-3">
                            <button
                                onClick={handleSearch}
                                disabled={isSearching}
                                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                            >
                                {isSearching && (
                                    <svg
                                        className="animate-spin h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        />
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        />
                                    </svg>
                                )}
                                {isSearching ? "Searching..." : "Search"}
                            </button>
                            <button
                                onClick={handleReset}
                                disabled={isSearching}
                                className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors"
                            >
                                Reset
                            </button>

                            {searchError && (
                                <div className="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p className="text-sm text-red-600">
                                        {searchError}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Results */}
                {results.data.length > 0 && (
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 border-b border-gray-200">
                            <div className="flex justify-between items-center">
                                <h2 className="text-lg font-semibold text-gray-700">
                                    Results: {results.meta.total} event
                                    {results.meta.total !== 1 ? "s" : ""} found
                                </h2>
                                <button
                                    onClick={handleExport}
                                    className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                                >
                                    Export CSV
                                </button>
                            </div>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        {selectedFields.map((fieldKey) => {
                                            const field = availableFields.find(
                                                (f) => f.key === fieldKey
                                            );
                                            return (
                                                <th
                                                    key={fieldKey}
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    {field?.label || fieldKey}
                                                </th>
                                            );
                                        })}
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {results.data.map((result) => (
                                        <tr
                                            key={result.id}
                                            className="hover:bg-gray-50"
                                        >
                                            {selectedFields.map((fieldKey) => (
                                                <td
                                                    key={`${result.id}-${fieldKey}`}
                                                    className="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                                >
                                                    {getFieldValue(
                                                        result,
                                                        fieldKey
                                                    )}
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Pagination */}
                {results.data.length > 0 && (
                    <Pagination links={results.meta.links} />
                )}

                {/* Empty State */}
                {hasSearched && results.data.length === 0 && (
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-12 text-center text-gray-500">
                            <p className="text-lg">No events found</p>
                            <p className="text-sm mt-2">
                                Try adjusting your filters or search criteria.
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </EmptyLayout>
    );
};

export default EventSearchPage;
