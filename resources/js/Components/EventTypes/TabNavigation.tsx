import { Tab } from "@headlessui/react";

import { PageContent } from "@/Components/EventTypes/PageContent";
import {
    tabClasses,
    tabListClasses,
    tabPanelClasses,
} from "@/helpers/tabClasses";

export const TabNavigation = ({
    pages,
    selectedTab,
    onChange,
}: {
    pages: Array<App.Data.EventType.GuidePageData>;
    selectedTab: number;
    onChange: (index: number) => void;
}) => (
    <Tab.Group selectedIndex={selectedTab} onChange={onChange}>
        <Tab.List
            className={`${tabListClasses} flex-col md:flex-row text-left md:text-center space-x-0 md:space-x-4`}
        >
            {pages.map((page, index) => (
                <Tab
                    key={index}
                    className={`${tabClasses} text-left md:text-center`}
                >
                    {page.page_title}
                </Tab>
            ))}
        </Tab.List>
        <Tab.Panels className={tabPanelClasses}>
            {pages.map((page, index) => (
                <Tab.Panel key={index} className="prose">
                    <PageContent content={page.page_content} />
                </Tab.Panel>
            ))}
        </Tab.Panels>
    </Tab.Group>
);
