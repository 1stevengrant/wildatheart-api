import React from "react";

import { Link } from "@inertiajs/react";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { FireNamingGuidelines } from "@/Components/Events/FireNamingGuidelines";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import { FrequencyOptions } from "@/Components/FrequencyOptions";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { eventLocationFrom, useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import type { SharedUser } from "@/types";

interface EditFireFormProps {
    event: App.Data.EventData;
    user: SharedUser;
}

export const EditFireForm = ({ event, user }: EditFireFormProps) => {
    const eventTypeName = event.event_type_data.name;

    const { data, errors, put, updateLocationData, setData } =
        useEventForm<App.Data.FireEventData>({
            ...eventLocationFrom(event),
            name: event.name || `${user.given_name}'s`,
            frequency: event.frequency ?? "monthly",
            is_private: event.is_private,
        });

    const {
        mapRef,
        marker,
        handleMapClick,
        handleUserLevelZoom,
        selectPlace,
        locationError,
    } = useEventLocation({
        initialLocation: {
            latitude: event.latitude,
            longitude: event.longitude,
        },
        onLocationChange: updateLocationData,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route("update.event", event.id));
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <InputLabel
                    htmlFor="name"
                    value={`Name of ${singular(eventTypeName)}`}
                    required
                />
                <TextInput
                    id="name"
                    name="name"
                    value={data.name}
                    autoComplete="off"
                    isFocused={true}
                    onChange={(e) => setData("name", e.target.value)}
                />
                <Instruction>
                    This is the name that will be displayed on your Fire page.
                </Instruction>
                <InputError message={errors.name} />
                <FireNamingGuidelines />
            </div>

            <div className="mt-4">
                <InputLabel
                    htmlFor="frequency"
                    value={singular(eventTypeName) + " frequency"}
                    required
                />
                <select
                    id="frequency"
                    name="frequency"
                    value={data.frequency || ""}
                    className="select-input"
                    onChange={(e) => setData("frequency", e.target.value)}
                >
                    <FrequencyOptions />
                </select>
                <Instruction>
                    How often do you want to host this Fire event?
                </Instruction>
                <InputError message={errors.frequency} />
            </div>

            <MapboxSearch className="mt-4" onSelect={selectPlace} />

            <EventMap
                mapRef={mapRef}
                marker={marker}
                onMapClick={handleMapClick}
                onZoomEnd={() =>
                    handleUserLevelZoom(mapRef.current?.getZoom() || 6)
                }
                isFire
                className="mt-4"
            />
            <InputError message={locationError ?? undefined} />

            <div className="mt-4 space-x-4">
                <PrimaryButton>
                    Update This {singular(eventTypeName)}
                </PrimaryButton>
                <Link
                    className="text-n40 inline-block text-center"
                    href={route("my.events.active")}
                >
                    Cancel
                </Link>
            </div>
        </form>
    );
};
