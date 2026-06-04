import React, { useState, useEffect, useRef } from "react";

import { usePage, router } from "@inertiajs/react";
import pickBy from "lodash/pickBy";

import SelectInput from "@/Components/Forms/SelectInput";

function usePrevious<T>(value: T): T | undefined {
    const ref = useRef<T | undefined>(undefined);
    useEffect(() => {
        ref.current = value;
    });
    return ref.current;
}

export const FilterBar = () => {
    const { filters, myEvents } = usePage<{
        filters: { event_id?: string; search?: string; trashed?: string };
        myEvents: { id: string; name: string }[];
    }>().props;
    const allEvents = { id: "", name: "All Events" };
    const events = myEvents.map(({ id, name }) => ({ value: id, label: name }));
    const mergedEvents = [
        { value: allEvents.id, label: allEvents.name },
        ...events,
    ];

    const [values, setValues] = useState({
        event_id: filters.event_id || "",
    });

    const prevValues = usePrevious(values);

    const reset = () => {
        setValues({
            event_id: "",
        });
    };

    useEffect(() => {
        if (prevValues) {
            const query = Object.keys(pickBy(values)).length
                ? pickBy(values)
                : {};

            router.get(route(route().current() as string), query, {
                replace: true,
                preserveState: true,
            });
        }
    }, [values]);

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
    ) => {
        const name = e.target.name;
        const value = e.target.value;

        setValues((values) => ({
            ...values,
            [name]: value,
        }));
    };

    return (
        <div className="flex items-center w-full max-w-md py-2">
            <SelectInput
                name="event_id"
                value={values.event_id}
                onChange={handleChange}
                options={mergedEvents}
            />

            <button
                onClick={reset}
                className="ml-3 text-sm focus:outline-none"
                type="button"
            >
                Reset
            </button>
        </div>
    );
};
