import React, { useEffect } from "react";

import { Link } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { FireNamingGuidelines } from "@/Components/Events/FireNamingGuidelines";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import TextInput from "@/Components/Forms/TextInput";
import { FrequencyOptions } from "@/Components/FrequencyOptions";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { eventIsAFire } from "@/helpers/eventIsAFire";
import { useDates } from "@/Hooks/useDates";
import { useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import useRoute from "@/Hooks/useRoute";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const EditEventPage = () => {
    const { startDate, maxDate, handleStartDateChange, setStartDate } =
        useDates();
    const page = useTypedPage();
    const route = useRoute();

    const user = page.props.auth.user!;
    const event = page.props.event as App.Data.EventData;
    const eventTypeName = event.event_type_data.name;
    const pageTitle = `Editing ${event.name}`;

    // Custom hooks
    const {
        data,
        errors,
        put,
        isPrivate,
        handlePrivacyToggle,
        updateLocationData,
        setData,
    } = useEventForm({
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
            is_private: event.is_private,
            start_date: event.start_date,
            frequency: event.frequency,
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
        // Set initial start date
        if (dayjs(event.start_date).isValid()) {
            setStartDate(dayjs(event.start_date?.split("T")[0]).toDate());
        }
    }, [event.start_date, setStartDate]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route("update.event", event.id));
    };

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>
                {eventIsAFire(event.event_type_slug)
                    ? `Editing ${event.name}`
                    : `Editing ${event.event_type_data.name}`}
            </HeadingOne>

            <form onSubmit={handleSubmit}>
                {eventIsAFire(event.event_type_slug) && (
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
                            This is the name that will be displayed on your Fire
                            page.
                        </Instruction>
                        <InputError message={errors.name} />
                        <FireNamingGuidelines />
                    </div>
                )}

                {!eventIsAFire(event.event_type_slug) && (
                    <div className="mt-4">
                        <DatePickerInput
                            label="Start date"
                            placeholder="Select start date"
                            minDate={new Date()}
                            value={startDate}
                            maxDate={maxDate}
                            clearable
                            required
                            onChange={(date) =>
                                handleStartDateChange(date, setData)
                            }
                        />
                        <InputError message={errors.start_date} />
                    </div>
                )}

                {eventIsAFire(event.event_type_slug) && (
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
                            onChange={(e) =>
                                setData(
                                    "frequency",
                                    e.target
                                        .value as App.Enums.Event.FrequencyEnum
                                )
                            }
                        >
                            <FrequencyOptions />
                        </select>
                        <Instruction>
                            How often do you want to host this Fire event?
                        </Instruction>
                        <InputError message={errors.frequency} />
                    </div>
                )}

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

                {!eventIsAFire(event.event_type_slug) && (
                    <div className="mt-4">
                        <label className="flex items-center space-x-4 hover:cursor-pointer">
                            <SwitchComponent
                                checked={isPrivate}
                                onChange={handlePrivacyToggle}
                            />
                            <div className="text-white text-sm mt-4">
                                <div>I want this event to be private.</div>
                                <div>
                                    The event will not be shown on map or
                                    directory.
                                </div>
                            </div>
                        </label>
                    </div>
                )}

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
        </HostDashboardLayout>
    );
};

export default EditEventPage;
