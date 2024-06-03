import React from "react";

import {
    FolderPlusIcon,
    VideoCameraIcon,
} from "@heroicons/react/24/outline";

import { ListItem } from "@/Components/EventResources/ListItem";
import { MyProfileWrapper } from "@/Components/Layout/Elements/MyProfileWrapper";
import { eventTypeBackground } from "@/helpers/eventTypeBackground";
import { imageGenerator } from "@/helpers/user";
import useTypedPage from "@/Hooks/useTypedPage";
import { AppLayout } from "@/Layouts/AppLayout";
import { EventType } from "@/types/EventType";

const ResourcesPage = () => {
    const page = useTypedPage();
    const eventTypes = page.props.eventTypes as EventType[];
    const pageTitle = "Event Resources";
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <div
                    className={`grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-4 max-w-5xl`}
                >
                    {eventTypes.map((eventType: EventType) => (
                        <div
                            key={eventType.id}
                            className={`p-6 bg-gradient-to-br ${eventTypeBackground(eventType.slug)} end-zinc-900 rounded-2xl shadow-xl`}
                        >
                            <img
                                alt={eventType.name}
                                className={`rounded-xl`}
                                src={imageGenerator(eventType.image, {
                                    width: 200,
                                    height: 200,
                                })}
                            />
                            <ul
                                className={`text-sm font-medium mt-4 space-y-3`}
                            >
                                <ListItem
                                    icon={
                                        <FolderPlusIcon
                                            className={`w-6 h-6 mr-2`}
                                        />
                                    }
                                    text={`Event Guide`}
                                    url={``}
                                />

                                <ListItem
                                    icon={
                                        <VideoCameraIcon
                                            className={`w-6 h-6 mr-2`}
                                        />
                                    }
                                    text={`Videos`}
                                    url={``}
                                />
                            </ul>
                        </div>
                    ))}
                </div>
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default ResourcesPage;
