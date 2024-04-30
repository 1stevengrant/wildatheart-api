import React from 'react';
import useTypedPage from "@/Hooks/useTypedPage";
import EventInformation from "@/Components/Events/EventInformation";
import { EmptyLayout }from "@/Layouts/EmptyLayout";

const HostEvent = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Your Fire Information`}>
            <EventInformation user={page.props.auth.user!}/>
        </EmptyLayout>
    )
}

export default HostEvent;
