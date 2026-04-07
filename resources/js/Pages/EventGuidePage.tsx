import { useCallback, useState } from "react";

import { EventHeader } from "@/Components/EventTypes/EventTypeHeader";
import { TabNavigation } from "@/Components/EventTypes/TabNavigation";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { GuidePage } from "@/types/EventType";

const slugify = (text: string) =>
    text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/(^-|-$)/g, "");

const getInitialTab = (pages: Array<GuidePage>) => {
    const hash = window.location.hash.slice(1);
    if (!hash) {
        return 0;
    }
    const index = pages.findIndex((p) => slugify(p.page_title) === hash);
    return index >= 0 ? index : 0;
};

const EventGuidePage = () => {
    const page = useTypedPage();
    const eventType = page.props.eventType as App.Data.EventTypeData;
    const pageTitle = `${eventType.name} Guide`;
    const pages = eventType.event_guide_pages ?? [];
    const [selectedTab, setSelectedTab] = useState(() =>
        getInitialTab(pages),
    );

    const handleTabChange = useCallback(
        (index: number) => {
            setSelectedTab(index);
            const slug = slugify(pages[index].page_title);
            window.history.replaceState(null, "", `#${slug}`);
        },
        [pages],
    );

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <EventHeader title={pageTitle} image={eventType.image} />
            {pages.length > 0 && (
                <TabNavigation
                    pages={pages}
                    selectedTab={selectedTab}
                    onChange={handleTabChange}
                />
            )}
        </HostDashboardLayout>
    );
};

export default EventGuidePage;
