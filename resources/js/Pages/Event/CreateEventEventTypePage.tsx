import React from "react";

import { ArrowRightIcon, ArrowLeftIcon } from "@heroicons/react/16/solid";
import { Head, Link } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { EventMap } from "@/Components/EventMap";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import SelectInput from "@/Components/Forms/SelectInput";
import { SwitchComponent } from "@/Components/Forms/SwitchComponent";
import { EmptyLayoutBody } from "@/Components/Layout/Elements/EmptyLayoutBody";
import { EmptyLayoutFooter } from "@/Components/Layout/Elements/EmptyLayoutFooter";
import { MapboxSearch } from "@/Components/Maps/MapboxSearch";
import { ConfirmEventDetailsModal } from "@/Components/Modals/ConfirmEventDetailsModal";
import { PageContainer } from "@/Components/Pages/Event/PageContainer";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { Spinner } from "@/Components/Ui/Spinner";
import {
    fromCalendarDate,
    latestEventStartDate,
    toCalendarDate,
} from "@/helpers/calendarDate";
import { circularButtonClasses } from "@/helpers/circularButtonClasses";
import { LOCATION_TBC } from "@/helpers/locationTbc";
import { scrollToError } from "@/helpers/scrollToError";
import { useEventForm } from "@/Hooks/useEventForm";
import { useEventLocation } from "@/Hooks/useEventLocation";
import { useEventModal } from "@/Hooks/useEventModal";
import useTypedPage from "@/Hooks/useTypedPage";
import { SidebarLayout } from "@/Layouts/SidebarLayout";

const CreateEventEventTypePage = () => {
    const page = useTypedPage();
    const { materialOrderItems, eventType } = page.props as any;
    const { user } = page.props.auth as any;

    const {
        data,
        processing,
        errors,
        post,
        isPrivate,
        handlePrivacyToggle,
        updateLocationData,
        setData,
    } = useEventForm({ user, eventType });

    const { mapRef, marker, handleMapClick, handleUserLevelZoom, selectPlace } =
        useEventLocation({
            onLocationChange: updateLocationData,
        });

    const hasLocation = data.latitude !== null;

    const {
        confirmModalOpen,
        setConfirmModalOpen,
        openConfirmModal,
        closeConfirmModal,
    } = useEventModal();

    const confirmSubmit = () => {
        closeConfirmModal();
        post(
            route("host.eventType.create.store", { eventType: eventType.slug }),
            {
                preserveScroll: true,
                onError: () => scrollToError(),
            }
        );
    };

    return (
        <>
            <Head title="Create Event" />
            <SidebarLayout
                footer={
                    <EmptyLayoutFooter>
                        <Link
                            href={route("host.eventType.mailing", eventType)}
                            className={circularButtonClasses}
                        >
                            <ArrowLeftIcon className="h-4 w-4" />
                        </Link>
                        <PrimaryButton
                            onClick={openConfirmModal}
                            disabled={processing || !hasLocation}
                        >
                            {processing && <Spinner />}
                            {hasLocation
                                ? "Create Event & See Dashboard"
                                : "Pick a location to continue"}
                            <ArrowRightIcon className="ml-2 h-4 w-4" />
                        </PrimaryButton>
                    </EmptyLayoutFooter>
                }
            >
                <EmptyLayoutBody>
                    <PageContainer>
                        <HeadingOne>Create Event</HeadingOne>
                        <p>
                            The below information can be changed at any time.
                            Once published, attendees will be able to see your
                            event on the directory map and reach out to you for
                            more info.
                        </p>

                        <form>
                            <MapboxSearch
                                className="mt-4"
                                onSelect={selectPlace}
                            />

                            <EventMap
                                mapRef={mapRef}
                                marker={marker}
                                onMapClick={handleMapClick}
                                onZoomEnd={() =>
                                    handleUserLevelZoom(
                                        mapRef.current?.getZoom() || 6
                                    )
                                }
                                className="mt-4"
                                height="h-[300px] md:h-[40vh]"
                            />

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
                                        setData(
                                            "start_date",
                                            toCalendarDate(date)
                                        )
                                    }
                                />
                                <InputError message={errors.start_date} />

                                <label className="flex items-center space-x-4 hover:cursor-pointer">
                                    <SwitchComponent
                                        checked={isPrivate}
                                        onChange={handlePrivacyToggle}
                                    />
                                    <div className="text-white text-sm mt-4">
                                        <div>
                                            I want this event to be private.
                                        </div>
                                        <div>
                                            The event will not be shown on map
                                            or directory.
                                        </div>
                                    </div>
                                </label>

                                {Boolean(eventType.allow_materials_order) && (
                                    <div className="mt-4">
                                        <p>
                                            We'll send you free{" "}
                                            {singular(eventType.name)}{" "}
                                            note-taking guides for your
                                            attendees. Just tell us how many you
                                            need.
                                        </p>
                                        <InputLabel
                                            htmlFor="number_of_items_in_material_order"
                                            value={
                                                eventType.number_of_items_in_material_order_label ??
                                                "Number of items in material order"
                                            }
                                            required
                                        />
                                        <SelectInput
                                            id="number_of_items_in_material_order"
                                            onChange={(e) =>
                                                setData(
                                                    "number_of_items_in_material_order",
                                                    e.target.value
                                                )
                                            }
                                            className="text-white mt-2"
                                            options={materialOrderItems}
                                        />
                                        <InputError
                                            message={
                                                errors.number_of_items_in_material_order
                                            }
                                        />
                                    </div>
                                )}
                            </div>
                        </form>
                    </PageContainer>
                </EmptyLayoutBody>

                <ConfirmEventDetailsModal
                    open={confirmModalOpen}
                    setOpen={setConfirmModalOpen}
                    data={data}
                    eventTypeName={eventType.name}
                    eventLocation={data.city || LOCATION_TBC}
                    processing={processing}
                    onConfirm={confirmSubmit}
                />
            </SidebarLayout>
        </>
    );
};

export default CreateEventEventTypePage;
