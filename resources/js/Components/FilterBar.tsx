import React, { useState } from "react";

import { router } from "@inertiajs/react";
import pickBy from "lodash/pickBy";

import SelectInput from "@/Components/Forms/SelectInput";
import useTypedPage from "@/Hooks/useTypedPage";

export const FilterBar = () => {
    const { filters, myEvents } = useTypedPage<{
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

    const updateValues = (nextValues: typeof values) => {
        setValues(nextValues);

        router.get(route(route().current() as string), pickBy(nextValues), {
            replace: true,
            preserveState: true,
        });
    };

    const reset = () => {
        updateValues({
            event_id: "",
        });
    };

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
    ) => {
        updateValues({
            ...values,
            [e.target.name]: e.target.value,
        });
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
