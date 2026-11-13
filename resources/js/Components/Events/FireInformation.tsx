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
import { emptyEventLocation, useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import { useEventModal } from "@/Hooks/useEventModal";
import useTypedPage from "@/Hooks/useTypedPage";

export const FireInformation = ({ user }: any) => {
    const [startDatePosition, setStartDatePosition] = useState(0);
    const page = useTypedPage();
    const eventType = page.props.eventType as any;

    const { data, setData, post, processing, errors, updateLocationData } =
        useEventForm<App.Data.FireEventData>({
            ...emptyEventLocation,
            name: `${user.given_name}'s`,
            frequency: "monthly",
            is_private: false,
        });

    const {
        mapRef,
        marker,
        handleMapClick,
        handleUserLevelZoom,
        selectPlace,
        locationError,
    } = useEventLocation({
        onLocationChange: updateLocationData,
    });

    const hasLocation = data.latitude !== null;
    const eventLocation = data.city || LOCATION_TBC;

    const preview = {
        image: eventType.image,
        name: `${data.name} ${singular(eventType.name)}`,
        badge: data.frequency,
        location: eventLocation,
        hostName: user.fullname,
        hostPhoto: user.image,
    };

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
        const startDateElement = document.getElementById("frequency-field");
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
                                htmlFor="name"
                                value="Name Your Fire"
                                required
                            />
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                autoComplete="off"
                                isFocused={true}
                                onChange={(e) =>
                                    setData("name", e.target.value)
                                }
                            />
                            <Instruction>
                                This is the name that will be displayed on your
                                Fire page.
                            </Instruction>
                            <InputError message={errors.name} />
                        </div>

                        <FireNamingGuidelines />

                        <div className="mt-4" id="frequency-field">
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

                        <EventDisplay className="md:hidden mt-4" {...preview} />

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
                            isFire
                            className="mt-4"
                        />
                        <InputError message={locationError ?? undefined} />

                        <div className="mt-4 space-y-4">
                            <PrimaryButton
                                disabled={processing || !hasLocation}
                                className="w-full flex items-center justify-center"
                                type="submit"
                            >
                                {processing && <Spinner />}
                                {hasLocation
                                    ? "Next"
                                    : "Pick a location to continue"}
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
                            {...preview}
                        />
                    </div>
                </RightColumn>
            </TwoColumnWrapper>

            <FireConfirmationModal
                open={confirmModalOpen}
                setOpen={setConfirmModalOpen}
                data={data}
                eventLocation={eventLocation}
                processing={processing}
                onConfirm={confirmSubmit}
            />
        </>
    );
};
