import React, { useEffect } from "react";

import { Link } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { InputError } from "@/Components/Forms/InputError";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { useDates } from "@/Hooks/useDates";
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
    const { startDate, maxDate, handleStartDateChange, setStartDate } =
        useDates();
    const eventTypeName = event.event_type_data.name;

    const {
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
            start_date: event.start_date ?? undefined,
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

    useEffect(() => {
        if (dayjs(event.start_date).isValid()) {
            setStartDate(dayjs(event.start_date?.split("T")[0]).toDate());
        }
    }, [event.start_date, setStartDate]);

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
                    value={startDate}
                    maxDate={maxDate}
                    clearable
                    required
                    onChange={(date) => handleStartDateChange(date, setData)}
                />
                <InputError message={errors.start_date} />
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
