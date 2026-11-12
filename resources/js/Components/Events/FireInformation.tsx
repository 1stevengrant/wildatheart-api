import React, { useState, useEffect } from "react";

import { Link } from "@inertiajs/react";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { EventDisplay } from "@/Components/Events/EventDisplay";
import { FireNamingGuidelines } from "@/Components/Events/FireNamingGuidelines";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import { FrequencyOptions } from "@/Components/FrequencyOptions";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { FireConfirmationModal } from "@/Components/Modals/FireConfirmationModal";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { Spinner } from "@/Components/Ui/Spinner";
import { EventTypeCaptivatingFires } from "@/helpers/eventNames";
import { LOCATION_TBC } from "@/helpers/locationTbc";
import { scrollToError } from "@/helpers/scrollToError";
import { useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import { useEventModal } from "@/Hooks/useEventModal";
import { useEventState } from "@/Hooks/useEventState";
import useTypedPage from "@/Hooks/useTypedPage";

export const FireInformation = ({ user }: any) => {
    const [startDatePosition, setStartDatePosition] = useState(0);
    const page = useTypedPage();
    const eventType = page.props.eventType as any;

    // Custom hooks
    const { event, updateEvent, updateEventFromLocation } = useEventState({
        user,
        eventType,
    });

    const { data, setData, post, processing, errors, updateLocationData } =
        useEventForm({ user, eventType });

    const { mapRef, marker, handleMapClick, handleUserLevelZoom, selectPlace } =
        useEventLocation({
            onLocationChange: (locationData) => {
                updateLocationData(locationData);
                updateEventFromLocation(locationData);
            },
        });

    const {
        confirmModalOpen,
        setConfirmModalOpen,
        openConfirmModal,
        closeConfirmModal,
    } = useEventModal();

    const confirmSubmit = () => {
        closeConfirmModal();
        const eventPostEndpoint =
            eventType.slug === EventTypeCaptivatingFires
                ? route("host.captivating-fire.store")
                : route("host.fire.store");
        post(eventPostEndpoint, {
            preserveScroll: true,
            onError: () => scrollToError(),
        });
    };

    useEffect(() => {
        const startDateElement = document.getElementById("frequency");
        if (startDateElement) {
            const rect = startDateElement.getBoundingClientRect();
            setStartDatePosition(rect.top);
        }
    }, []);

    return (
        <>
            <TwoColumnWrapper>
                <LeftColumn>
                    <form
                        onSubmit={openConfirmModal}
                        className="max-w-[500px] mx-auto"
                    >
                        <HeadingOne className="my-12">
                            Tell us about your {singular(eventType.name)}
                        </HeadingOne>

                        <div>
                            <InputLabel
                                htmlFor="fire_name"
                                value="Name Your Fire"
                                required
                            />
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                autoComplete="off"
                                isFocused={true}
                                onChange={(e) => {
                                    setData("name", e.target.value);
                                    updateEvent({ name: e.target.value });
                                }}
                            />
                            <Instruction>
                                This is the name that will be displayed on your
                                Fire page.
                            </Instruction>
                            <InputError message={errors.name} />
                        </div>

                        <FireNamingGuidelines />

                        <div className="mt-4" id="frequency">
                            <InputLabel
                                htmlFor="frequency"
                                value={singular(eventType.name) + " frequency"}
                                required
                            />
                            <select
                                id="frequency"
                                name="frequency"
                                value={data.frequency || ""}
                                className="select-input"
                                onChange={(e) => {
                                    setData(
                                        "frequency",
                                        e.target
                                            .value as App.Enums.Event.FrequencyEnum
                                    );
                                    updateEvent({ frequency: e.target.value });
                                }}
                            >
                                <FrequencyOptions />
                            </select>
                            <Instruction>
                                How often do you want to host this Fire event?
                            </Instruction>
                            <InputError message={errors.frequency} />
                        </div>

                        <EventDisplay
                            className="md:hidden mt-4"
                            event={event}
                            frequency={data.frequency}
                        />

                        <MapboxSearch className="mt-4" onSelect={selectPlace} />

                        <EventMap
                            mapRef={mapRef}
                            marker={marker}
                            onMapClick={handleMapClick}
                            onZoomEnd={() =>
                                handleUserLevelZoom(
                                    mapRef.current?.getZoom() || 6
                                )
                            }
                            eventTypeSlug={eventType.slug}
                            className="mt-4"
                        />

                        <div className="mt-4 space-y-4">
                            <PrimaryButton
                                disabled={
                                    processing ||
                                    event.location === LOCATION_TBC
                                }
                                className="w-full flex items-center justify-center"
                                type="submit"
                            >
                                {processing && <Spinner />}
                                {event.location === LOCATION_TBC
                                    ? "Pick a location to continue"
                                    : "Next"}
                            </PrimaryButton>
                            <Link
                                className="text-n40 w-full inline-block text-center"
                                href={route("host.eventType.select")}
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </LeftColumn>

                <RightColumn className="!justify-start">
                    <div>
                        <EventDisplay
                            style={{ marginTop: startDatePosition }}
                            className="max-w-[350px] mx-auto hidden lg:flex"
                            event={event}
                            frequency={data.frequency}
                        />
                    </div>
                </RightColumn>
            </TwoColumnWrapper>

            <FireConfirmationModal
                open={confirmModalOpen}
                setOpen={setConfirmModalOpen}
                data={data}
                eventLocation={event.location}
                processing={processing}
                onConfirm={confirmSubmit}
            />
        </>
    );
};
