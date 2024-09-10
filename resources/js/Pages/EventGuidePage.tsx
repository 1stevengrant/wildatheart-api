import React, { useState } from "react";

import { Tab } from "@headlessui/react";

import { Accordion } from "@/Components/Accordion";
import {
    tabClasses,
    tabListClasses,
    tabPanelClasses,
} from "@/helpers/tabClasses";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { ImageSet } from "@/Sets/ImageSet";
import { TextSet } from "@/Sets/TextSet";
import { VideoSet } from "@/Sets/VideoSet";
import { EventType } from "@/types/EventType";

const EventGuidePage = () => {
    const page = useTypedPage();
    const eventType = page.props.eventType as EventType;
    const pageTitle = `${eventType.name} Guide`;
    const [selectedTab, setSelectedTab] = useState(0);
    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <div className={`flex items-center space-x-4 mb-4`}>
                {eventType.image && (
                    <img
                        alt={eventType.name}
                        className={`rounded-xl`}
                        src={imageGenerator(eventType.image, {
                            width: 150,
                            height: 150,
                        })}
                    />
                )}
                <h1 className={`text-[24px] font-bold leading-tight`}>
                    {pageTitle}
                </h1>
            </div>
            <Tab.Group selectedIndex={selectedTab} onChange={setSelectedTab}>
                <Tab.List
                    className={`${tabListClasses} flex-col md:flex-row text-left md:text-center space-x-0 md:space-x-4`}
                >
                    {eventType.event_guide_pages &&
                        eventType.event_guide_pages.map(
                            (page: any, index: number) => (
                                <Tab
                                    className={`${tabClasses} text-left md:text-center`}
                                    key={index}
                                >
                                    {page.page_title}
                                </Tab>
                            )
                        )}
                </Tab.List>
                <Tab.Panels className={tabPanelClasses}>
                    {eventType.event_guide_pages &&
                        eventType.event_guide_pages.map(
                            (page: any, index: number) => (
                                <Tab.Panel key={index} className={`prose`}>
                                    {page.page_content &&
                                        page.page_content.map(
                                            (block: any, index: number) => {
                                                switch (block.type) {
                                                    case "body_set":
                                                        return (
                                                            <TextSet
                                                                text={
                                                                    block.body
                                                                }
                                                                key={index}
                                                                className={`prose-p:text-white prose-strong:text-white prose-ol:text-white prose-ul:text-white prose-li:text-white prose-li:marker:text-white`}
                                                            />
                                                        );

                                                    case "video_set":
                                                        return (
                                                            <VideoSet
                                                                key={index}
                                                                video={
                                                                    block.video
                                                                }
                                                            />
                                                        );
                                                    case "image_set":
                                                        return (
                                                            <ImageSet
                                                                key={index}
                                                                image={
                                                                    block.image
                                                                }
                                                                params={{
                                                                    height: 400,
                                                                }}
                                                            />
                                                        );

                                                    case "accordion_set":
                                                        return (
                                                            <Accordion
                                                                key={index}
                                                                accordion={
                                                                    block.accordion
                                                                }
                                                            />
                                                        );
                                                    default:
                                                        return null;
                                                }
                                            }
                                        )}
                                </Tab.Panel>
                            )
                        )}
                </Tab.Panels>
            </Tab.Group>
        </HostDashboardLayout>
    );
};

export default EventGuidePage;
