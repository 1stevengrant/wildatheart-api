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
import { useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";

interface EditFireFormProps {
    event: App.Data.EventData;
    user: App.Data.UserData;
}

export const EditFireForm = ({ event, user }: EditFireFormProps) => {
    const eventTypeName = event.event_type_data.name;

    const { data, errors, put, updateLocationData, setData } =
        useEventForm<App.Data.FireEventData>({
            initialData: {
                name: event.name || `${user.given_name}'s`,
                state: event.state,
                latitude: event.latitude,
                longitude: event.longitude,
                country: event.country,
                region: event.region,
                district: event.district,
                locality: event.locality,
                neighborhood: event.neighborhood,
                street_address: event.street_address,
                city: event.city,
                zip: event.zip,
                is_private: event.is_private ?? undefined,
                frequency: event.frequency ?? undefined,
            },
            user,
        });

    const { mapRef, marker, handleMapClick, handleUserLevelZoom } =
        useEventLocation({
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

            <MapboxSearch className="mt-4" />

            <EventMap
                mapRef={mapRef}
                marker={marker}
                onMapClick={handleMapClick}
                onZoomEnd={() =>
                    handleUserLevelZoom(mapRef.current?.getZoom() || 6)
                }
                eventTypeSlug={event.event_type_slug}
                className="mt-4"
            />

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
