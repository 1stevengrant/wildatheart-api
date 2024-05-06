import React from "react";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import { PageContainer } from "@/Components/Page/Event/PageContainer";

const CreateEventEventTypePage = () => {
    const handleSubmit = (e: React.FormEvent) => {};

    return (
        <PageContainer>
            <h1>Create Event</h1>
            <p>
                The below information can be changed at any time. Once
                published, attendees will be able to see your event on the
                directory map and reach out to you for more info.
            </p>
            <form onSubmit={(event) => handleSubmit(event)} className="mt-4">
                <PrimaryButton type="submit">
                    Create Event & View Dashboard
                </PrimaryButton>
            </form>
        </PageContainer>
    );
};

export default CreateEventEventTypePage;
