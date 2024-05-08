import React from "react";

import { EventInformation } from "@/Components/Events/EventInformation";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import useTypedPage from "@/Hooks/useTypedPage";

const CreateEventEventTypePage = () => {
    const page = useTypedPage();

    return (
        <PageContainer>
            <h1>Create Event</h1>
            <p>
                The below information can be changed at any time. Once
                published, attendees will be able to see your event on the
                directory map and reach out to you for more info.
            </p>

            <EventInformation user={page.props.auth.user!} />
        </PageContainer>
    );
};

export default CreateEventEventTypePage;
