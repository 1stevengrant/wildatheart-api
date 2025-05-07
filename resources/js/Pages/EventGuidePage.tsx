import { useState } from "react";

import { EventHeader } from "@/Components/EventTypes/EventTypeHeader";
import { TabNavigation } from "@/Components/EventTypes/TabNavigation";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const EventGuidePage = () => {
    const page = useTypedPage();
    const eventType = page.props.eventType as App.Data.EventTypeData;
    const pageTitle = `${eventType.name} Guide`;
    const [selectedTab, setSelectedTab] = useState(0);

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <EventHeader title={pageTitle} image={eventType.image} />
            {eventType.event_guide_pages && (
                <TabNavigation
                    pages={eventType.event_guide_pages}
                    selectedTab={selectedTab}
                    onChange={setSelectedTab}
                />
            )}
        </HostDashboardLayout>
    );
};

export default EventGuidePage;
