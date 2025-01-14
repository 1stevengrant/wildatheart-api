import React, { useState } from "react";

import { EventGuideHeader } from "@/Components/Events/EventGuideHeader";
import { TabNavigation } from "@/Components/TabNavigation";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { EventType } from "@/types/EventType";

const EventGuidePage = () => {
    const page = useTypedPage();
    const eventType = page.props.eventType as EventType;
    const pageTitle = `${eventType.name} Guide`;
    const [selectedTab, setSelectedTab] = useState(0);

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <EventGuideHeader title={pageTitle} image={eventType.image} />
            {eventType.event_guide_pages && (
                <TabNavigation
                    pages={eventType.event_guide_pages}
                    selectedTab={selectedTab}
                    onTabChange={setSelectedTab}
                />
            )}
        </HostDashboardLayout>
    );
};

export default EventGuidePage;
