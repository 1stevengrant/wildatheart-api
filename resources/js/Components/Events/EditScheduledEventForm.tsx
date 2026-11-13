import React from "react";

import { Link } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { InputError } from "@/Components/Forms/InputError";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import {
    fromCalendarDate,
    latestEventStartDate,
    toCalendarDate,
} from "@/helpers/calendarDate";
import { useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import type { SharedUser } from "@/types";

interface EditScheduledEventFormProps {
    event: App.Data.EventData;
    user: SharedUser;
}

export const EditScheduledEventForm = ({
    event,
    user,
}: EditScheduledEventFormProps) => {
    const eventTypeName = event.event_type_data.name;

    const {
        data,
        errors,
        put,
        isPrivate,
        handlePrivacyToggle,
        updateLocationData,
        setData,
    } = useEventForm<App.Data.UpdateEventData>({
        initialData: {
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
            start_date: event.start_date?.split("T")[0],
        },
        user,
    });

    const { mapRef, marker, handleMapClick, handleUserLevelZoom, selectPlace } =
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
            <div className="mt-4">
                <DatePickerInput
                    label="Start date"
                    placeholder="Select start date"
                    minDate={new Date()}
                    value={fromCalendarDate(data.start_date)}
                    maxDate={latestEventStartDate()}
                    clearable
                    required
                    onChange={(date) =>
                        setData("start_date", toCalendarDate(date) ?? "")
                    }
                />
                <InputError message={errors.start_date} />
            </div>

            <MapboxSearch className="mt-4" onSelect={selectPlace} />

            <EventMap
                mapRef={mapRef}
                marker={marker}
                onMapClick={handleMapClick}
                onZoomEnd={() =>
                    handleUserLevelZoom(mapRef.current?.getZoom() || 6)
                }
                className="mt-4"
            />

            <div className="mt-4">
                <label className="flex items-center space-x-4 hover:cursor-pointer">
                    <SwitchComponent
                        checked={isPrivate}
                        onChange={handlePrivacyToggle}
                    />
                    <div className="text-white text-sm mt-4">
                        <div>I want this event to be private.</div>
                        <div>
                            The event will not be shown on map or directory.
                        </div>
                    </div>
                </label>
            </div>

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
