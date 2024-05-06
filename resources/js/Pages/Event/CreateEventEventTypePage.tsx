import React, { useRef, useState } from "react";

import { DatePickerInput } from "@mantine/dates";
import dayjs from "dayjs";
import Map, { MapRef } from "react-map-gl/maplibre";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { SwitchComponent } from "@/Components/SwitchComponent";
import { useDates } from "@/Hooks/useDates";

const CreateEventEventTypePage = () => {
    const maxDate = dayjs().add(1, "year").toDate();
    const { startDate, setStartDate } = useDates();
    const handleDateChange = (value: Date | null) => {
        setStartDate(value);
    };
    const [isPrivateEvent, setIsPrivateEvent] = useState(false);
    const handleSubmit = (e: React.FormEvent) => {};

    const mapRef = useRef<MapRef>(null);

    return (
        <PageContainer>
            <h1>Create Event</h1>
            <p>
                The below information can be changed at any time. Once
                published, attendees will be able to see your event on the
                directory map and reach out to you for more info.
            </p>

            <form onSubmit={(event) => handleSubmit(event)} className="mt-4">
                <MapboxSearch />
                <Map
                    ref={mapRef}
                    maxPitch={0}
                    minPitch={0}
                    pitchWithRotate={false}
                    dragRotate={false}
                    touchPitch={false}
                    customAttribution={``}
                    mapStyle={`https://api.maptiler.com/maps/streets/style.json?key=sr5EVCtJBfz1IQnASSwn`}
                />
                <div className={`mt-4`}>
                    <DatePickerInput
                        label="Start date"
                        placeholder="Select start date"
                        minDate={new Date()}
                        value={startDate}
                        maxDate={maxDate}
                        clearable
                        onChange={(value) => handleDateChange(value)}
                    />
                </div>
                <label
                    className={`flex items-center space-x-2 hover:cursor-pointer mt-4`}
                >
                    <SwitchComponent
                        checked={isPrivateEvent}
                        onChange={() => setIsPrivateEvent(!isPrivateEvent)}
                    />
                    <div className={`text-white text-sm pl-2`}>
                        <div>I want this event to be private.</div>
                        <div>
                            The event will not be shown on map or directory.
                        </div>
                    </div>
                </label>
                <div className={`mt-4`}>
                    <PrimaryButton type="submit">
                        Create Event & View Dashboard
                    </PrimaryButton>
                </div>
            </form>
        </PageContainer>
    );
};

export default CreateEventEventTypePage;
