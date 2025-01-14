import { Tab } from "@headlessui/react";

import { ContentBlock } from "@/Components/ContentBlock";
import {
    tabClasses,
    tabListClasses,
    tabPanelClasses,
} from "@/helpers/tabClasses";
import { EventGuidePageInterface } from "@/types/EventGuidePageInterface";
interface TabNavigationPropsInterface {
    pages: EventGuidePageInterface[];
    selectedTab: number;
    onTabChange: (index: number) => void;
}

export const TabNavigation = ({
    pages,
    selectedTab,
    onTabChange,
}: TabNavigationPropsInterface) => (
    <Tab.Group selectedIndex={selectedTab} onChange={onTabChange}>
        <Tab.List
            className={`${tabListClasses} flex-col md:flex-row text-left md:text-center space-x-0 md:space-x-4`}
        >
            {pages?.map((page, index) => (
                <Tab
                    key={index}
                    className={`${tabClasses} text-left md:text-center ui-selected:font-bold`}
                >
                    {page.page_title}
                </Tab>
            ))}
        </Tab.List>
        <Tab.Panels className={tabPanelClasses}>
            {pages?.map((page, index) => (
                <Tab.Panel key={index} className="prose">
                    {page.page_content
                        ?.filter((block) => block.enabled)
                        .map((block, blockIndex) => (
                            <ContentBlock key={blockIndex} block={block} />
                        ))}
                </Tab.Panel>
            ))}
        </Tab.Panels>
    </Tab.Group>
);
