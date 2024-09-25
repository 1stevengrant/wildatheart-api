import React from "react";

import { usePage } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { SwitchComponentWrapper } from "@/Components/Forms/SwitchComponentWrapper";
import { useFilterState } from "@/Hooks/useFilterState";
import { filterState } from "@/store/filtersAtom";
import { PaginatedData } from "@/types";
import { EventType } from "@/types/EventType";

interface EventTypeGroup {
    [key: string]: { id: number; name: string }[]; // Grouped event types by group_type
}

export const InertiaEventTypesFilter = () => {
    useFilterState();
    const [filters, setFilters] = useRecoilState(filterState);
    const { eventTypes } = usePage<{
        eventTypes: PaginatedData<EventType>;
    }>().props;

    const { data } = eventTypes;

    const handleTypesChange = (id: number) => {
        setFilters((prev) => ({
            ...prev,
            event_types: prev.event_types.includes(id)
                ? prev.event_types.filter((typeId) => typeId !== id) // Remove ID if already selected
                : [...prev.event_types, id], // Add ID if not selected
        }));
    };

    // Group event types by group_type and type the accumulator properly
    const eventTypeGroups: EventTypeGroup = data.reduce(
        (acc: EventTypeGroup, eventType: EventType) => {
            const { group_type, id, name } = eventType;
            if (!acc[group_type]) acc[group_type] = [];
            acc[group_type].push({ id, name });
            return acc;
        },
        {} as EventTypeGroup
    );

    return (
        <div>
            {Object.keys(eventTypeGroups).map((group_type, index) => (
                <div key={index}>
                    <h4 className="text-gray-700 font-semibold">
                        {group_type}
                    </h4>
                    <div className="space-y-2">
                        {eventTypeGroups[group_type].map((eventType, idx) => (
                            <SwitchComponentWrapper key={idx}>
                                <span>{eventType.name}</span>
                                <SwitchComponent
                                    checked={filters.event_types.includes(
                                        eventType.id
                                    )}
                                    onChange={() =>
                                        handleTypesChange(eventType.id)
                                    }
                                />
                            </SwitchComponentWrapper>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
};
