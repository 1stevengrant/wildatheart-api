import React from "react";

import { EventInformation } from "@/Components/Events/EventInformation";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const HostEventPage = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Your Fire Information`}>
            <EventInformation user={page.props.auth.user!} />
        </EmptyLayout>
    );
};

export default HostEventPage;
